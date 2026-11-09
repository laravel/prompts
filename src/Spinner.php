<?php

namespace Laravel\Prompts;

use Closure;
use RuntimeException;

class Spinner extends Prompt
{
    /**
     * How long to wait between rendering each frame.
     */
    public int $interval = 100;

    /**
     * The number of times the spinner has been rendered.
     */
    public int $count = 0;

    /**
     * Whether the spinner can only be rendered once.
     */
    public bool $static = false;

    /**
     * The process ID after forking.
     */
    protected int $pid;

    /**
     * Create a new Spinner instance.
     */
    public function __construct(public string $message = '')
    {
        //
    }

    /**
     * Render the spinner and execute the callback.
     *
     * @template TReturn of mixed
     *
     * @param  Closure(): TReturn  $callback
     * @return TReturn
     */
    public function spin(Closure $callback): mixed
    {
        $this->capturePreviousNewLines();

        if ($this->shouldRenderStatically()) {
            return $this->renderStatically($callback);
        }

        $originalAsync = pcntl_async_signals(true);
        /** @var callable|int $originalSignalHandler */
        $originalSignalHandler = pcntl_signal_get_handler(SIGINT);

        pcntl_signal(SIGINT, fn () => exit());

        try {
            $this->hideCursor();
            $this->render();

            $this->pid = pcntl_fork();

            if ($this->pid === 0) {
                while (true) { // @phpstan-ignore-line
                    $this->render();

                    $this->count++;

                    usleep($this->interval * 1000);
                }
            } else {
                $result = $callback();

                $this->resetTerminal($originalAsync, $originalSignalHandler);

                return $result;
            }
        } catch (\Throwable $e) {
            $this->resetTerminal($originalAsync, $originalSignalHandler);

            throw $e;
        }
    }

    /**
     * Determine whether the spinner should be rendered statically.
     *
     * Animating redraws the spinner with cursor movements, which only make
     * sense on a terminal. Decorated output alone is not enough, because
     * `--ansi` forces it in CI and build logs where each frame would be
     * written on its own line.
     */
    protected function shouldRenderStatically(): bool
    {
        return ! static::output()->isDecorated()
            || ! static::terminal()->isOutputTty()
            || ! function_exists('pcntl_fork')
            || ! function_exists('posix_kill');
    }

    /**
     * Reset the terminal.
     */
    protected function resetTerminal(bool $originalAsync, callable|int $originalSignalHandler = SIG_DFL): void
    {
        pcntl_async_signals($originalAsync);
        pcntl_signal(SIGINT, $originalSignalHandler);

        $this->eraseRenderedLines();
    }

    /**
     * Render a static version of the spinner.
     *
     * @template TReturn of mixed
     *
     * @param  Closure(): TReturn  $callback
     * @return TReturn
     */
    protected function renderStatically(Closure $callback): mixed
    {
        $this->static = true;

        try {
            $this->hideCursor();
            $this->render();

            $result = $callback();
        } finally {
            $this->eraseRenderedLines();
        }

        return $result;
    }

    /**
     * Disable prompting for input.
     *
     * @throws RuntimeException
     */
    public function prompt(): never
    {
        throw new RuntimeException('Spinner cannot be prompted.');
    }

    /**
     * Get the current value of the prompt.
     */
    public function value(): bool
    {
        return true;
    }

    /**
     * Clear the lines rendered by the spinner.
     */
    protected function eraseRenderedLines(): void
    {
        $lines = explode(PHP_EOL, $this->prevFrame);
        $this->moveCursor(-999, -count($lines) + 1);
        $this->eraseDown();
    }

    /**
     * Clean up after the spinner.
     */
    public function __destruct()
    {
        if (! empty($this->pid)) {
            posix_kill($this->pid, SIGHUP);
        }

        parent::__destruct();
    }
}
