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
     * The final message to display.
     *
     * @var string
     */
    public string $finalMessage = '';

    /**
     * A callback to generate the final message.
     *
     * @var string|\Closure(mixed): string
     */
    protected $finalMessageHandler;

    /**
     * Create a new Spinner instance.
     *
     * @param  string|\Closure(mixed): string  $finalMessageHandler
     */
    public function __construct(public string $message = '', string|Closure $finalMessageHandler = '')
    {
        $this->finalMessageHandler = $finalMessageHandler;
    }

    /**
     * Render the spinner and execute the callback.
     *
     * @template TReturn of mixed
     *
     * @param  \Closure(): TReturn  $callback
     * @return TReturn
     */
    public function spin(Closure $callback): mixed
    {
        $this->capturePreviousNewLines();

        if (!function_exists('pcntl_fork')) {
            return $this->renderStatically($callback);
        }

        $originalAsync = pcntl_async_signals(true);

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

                $this->finalMessage = $this->getFinalMessage($result);

                $this->resetTerminal($originalAsync);

                return $result;
            }
        } catch (\Throwable $e) {
            $this->resetTerminal($originalAsync);

            throw $e;
        }
    }

    /**
     * Get the final message to display.
     */
    protected function getFinalMessage(mixed $result): string
    {
        if ($this->finalMessageHandler === '') {
            return '';
        }

        if (is_callable($this->finalMessageHandler)) {
            return ($this->finalMessageHandler)($result) ?? '';
        }

        return $this->finalMessageHandler;
    }

    /**
     * Reset the terminal.
     */
    protected function resetTerminal(bool $originalAsync): void
    {
        pcntl_async_signals($originalAsync);
        pcntl_signal(SIGINT, SIG_DFL);

        $this->eraseRenderedLines();
    }

    /**
     * Render a static version of the spinner.
     *
     * @template TReturn of mixed
     *
     * @param  \Closure(): TReturn  $callback
     * @return TReturn
     */
    protected function renderStatically(Closure $callback): mixed
    {
        $this->static = true;

        try {
            $this->hideCursor();
            $this->render();

            $result = $callback();

            $this->finalMessage = $this->getFinalMessage($result);
        } finally {
            $this->eraseRenderedLines();
        }

        return $result;
    }

    /**
     * Disable prompting for input.
     *
     * @throws \RuntimeException
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
        if ($this->finalMessage !== '') {
            $this->render();
        } else {
            $lines = explode(PHP_EOL, $this->prevFrame);
            $this->moveCursor(-999, -count($lines) + 1);
            $this->eraseDown();
        }
    }

    /**
     * Clean up after the spinner.
     */
    public function __destruct()
    {
        if (!empty($this->pid)) {
            posix_kill($this->pid, SIGHUP);
        }

        parent::__destruct();
    }
}
