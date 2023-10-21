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
     * The sockets used to communicate between the spinner and the task.
     */
    protected SpinnerSockets $sockets;

    /**
     * The process ID after forking.
     */
    protected int $pid;

    /**
     * Whether the spinner has streamed output.
     */
    public bool $hasStreamingOutput = false;

    /**
     * A unique string to indicate that the spinner should stop.
     */
    public string $stopIndicator;

    /**
     * Create a new Spinner instance.
     */
    public function __construct(public string $message = '')
    {
        $this->stopIndicator = uniqid().uniqid().uniqid();
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

        if (! function_exists('pcntl_fork')) {
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
                    $this->setNewMessage();
                    $this->renderStreamedOutput();
                    $this->render();

                    $this->count++;

                    usleep($this->interval * 1000);
                }
            } else {
                $result = $callback($this->sockets->messenger());

                $this->sockets->messenger()->stop($this->stopIndicator);

                // Let the spinner finish its last cycle before exiting
                usleep($this->interval * 1000);

                // Read the last frame actually rendered from the spinner
                $realPrevFrame = $this->sockets->readPrevFrame();

                if ($realPrevFrame) {
                    $this->prevFrame = $realPrevFrame;
                }

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

        if ($output === '') {
            return;
        }

        $this->resetCursorPosition();
        $this->eraseDown();

        if (! $this->hasStreamingOutput && str_starts_with($this->prevFrame, PHP_EOL)) {
            // This is the first line of streaming output we're about to write
            static::writeDirectly(PHP_EOL);
        }

        $this->hasStreamingOutput = true;

        collect(explode(PHP_EOL, rtrim($output)))
            ->each(fn ($line) => $line === $this->stopIndicator ? null : static::writeDirectlyWithFormatting(' '.$line.PHP_EOL));

        $this->writeDirectly($this->prevFrame);

        if (str_contains($output, $this->stopIndicator)) {
            // Send the last frame actually rendered back to the parent process
            $this->sockets->sendPrevFrame($this->prevFrame);
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
