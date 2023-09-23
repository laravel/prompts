<?php

namespace Laravel\Prompts;

use Closure;
use Laravel\Prompts\Concerns\Colors;
use RuntimeException;

class Spinner extends Prompt
{
    use Colors;

    /**
     * How long to wait between rendering each frame.
     */
    public int $interval = 100;

    /**
     * The number of times the spinner has been rendered.
     */
    public int $count = 0;

    /**
     * Whether the spinner has streamed output.
     */
    public bool $hasStreamingOutput = false;

    /**
     * Whether the spinner can only be rendered once.
     */
    public bool $static = false;

    /**
     * The sockets used to communicate between the spinner and the task.
     */
    protected SpinnerSockets $sockets;

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
     * @param  \Closure(): TReturn  $callback
     * @return TReturn
     */
    public function spin(Closure $callback): mixed
    {
        $this->capturePreviousNewLines();

        $this->sockets = SpinnerSockets::create();

        register_shutdown_function(fn () => $this->restoreCursor());

        if (! function_exists('pcntl_fork')) {
            return $this->renderStatically($callback);
        }

        $originalAsync = pcntl_async_signals(true);

        pcntl_signal(SIGINT, fn () => exit());

        try {
            $this->hideCursor();
            $this->render();

            $pid = pcntl_fork();

            if ($pid === 0) {
                while (true) { // @phpstan-ignore-line
                    $this->setNewMessage();
                    $this->renderStreamedOutput();
                    $this->render();

                    $this->count++;

                    usleep($this->interval * 1000);
                }
            } else {
                register_shutdown_function(fn () => posix_kill($pid, SIGHUP));

                $result = $callback($this->sockets->messenger());

                posix_kill($pid, SIGHUP);

                $this->resetTerminal($originalAsync);

                return $result;
            }
        } catch (\Throwable $e) {
            $this->resetTerminal($originalAsync);

            throw $e;
        }
    }

    /**
     * Render any streaming output from the spinner, if available.
     */
    protected function renderStreamedOutput(): void
    {
        $output = $this->sockets->streamingOutput();

        if ($output !== '') {
            $this->hasStreamingOutput = true;

            $lines = count(explode(PHP_EOL, $this->prevFrame)) - 1;

            $this->moveCursor(-999, $lines * -1);
            $this->eraseDown();

            collect(explode(PHP_EOL, rtrim($output)))
                ->each(fn ($line) => static::writeDirectlyWithFormatting(' '.$line.PHP_EOL));

            static::writeDirectlyWithFormatting($this->dim(str_repeat('─', 60)));
            $this->writeDirectly($this->prevFrame);
        }
    }

    /**
     * Set the new message if one is available.
     */
    protected function setNewMessage(): void
    {
        $message = $this->sockets->message();

        if ($message !== '') {
            $this->message = $message;
        }
    }

    /**
     * Reset the terminal.
     */
    protected function resetTerminal(bool $originalAsync): void
    {
        pcntl_async_signals($originalAsync);
        pcntl_signal(SIGINT, SIG_DFL);

        $this->sockets->close();

        $this->eraseRenderedLines();
        $this->showCursor();
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
        } finally {
            $this->eraseRenderedLines();
            $this->showCursor();
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
        $lines = explode(PHP_EOL, $this->prevFrame);
        $this->moveCursor(-999, -count($lines) + 1);
        $this->eraseDown();
    }
}
