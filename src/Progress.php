<?php

namespace Laravel\Prompts;

use Closure;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

/**
 * @template TReturn of mixed
 */
class Progress extends Prompt
{
    /**
     * The current progress bar item count.
     */
    public int $progress = 0;

    /**
     * The total number of steps.
     */
    public int $total = 0;

    /**
     * The label for the current item.
     */
    public string $itemLabel = '';

    /**
     * The original value of pcntl_async_signals
     */
    protected bool $originalAsync;

    /**
     * Create a new ProgressBar instance.
     *
     * @template TStep of mixed
     *
     * @param  iterable<TStep>|int  $steps
     * @param  ?Closure(($steps is int ? int : TStep), Progress<TReturn>): TReturn  $callback
     */
    public function __construct(public string $label, public iterable|int $steps, public ?Closure $callback = null)
    {
        $this->total = match (true) {
            is_int($this->steps) => $this->steps,
            is_countable($this->steps) => count($this->steps),
            is_iterable($this->steps) => iterator_count($this->steps),
        };

        if ($this->total === 0) {
            throw new InvalidArgumentException('Progress bar must have at least one item.');
        }
    }

    /**
     * Display the progress bar.
     *
     * @return $this|array<TReturn>
     */
    public function display(): static|array
    {
        $this->capturePreviousNewLines();

        if ($this->callback === null) {
            return $this;
        }

        $this->start();

        $result = [];

        try {
            if (is_int($this->steps)) {
                for ($i = 0; $i < $this->steps; $i++) {
                    $result[] = ($this->callback)($i, $this);
                    $this->advance();
                }
            } else {
                foreach ($this->steps as $step) {
                    $result[] = ($this->callback)($step, $this);
                    $this->advance();
                }
            }
        } catch (Throwable $e) {
            $this->state = 'error';
            $this->render();
            $this->restoreCursor();
            $this->resetTerminal();

            throw $e;
        }

        if ($this->itemLabel !== '') {
            // Just pause for one moment to show the final item label
            // so it doesn't look like it was skipped
            usleep(250_000);
        }

        $this->finish();

        return $result;
    }

    /**
     * Start the progress bar.
     */
    public function start(): void
    {
        if (function_exists('pcntl_signal')) {
            $this->originalAsync = pcntl_async_signals(true);
            pcntl_signal(SIGINT, fn () => exit());
        }

        $this->state = 'active';
        $this->hideCursor();
        $this->render();
    }

    /**
     * Advance the progress bar.
     */
    public function advance(int $step = 1): void
    {
        $this->progress += $step;

        if ($this->progress > $this->total) {
            $this->progress = $this->total;
        }

        $this->render();
    }

    /**
     * Finish the progress bar.
     */
    public function finish(): void
    {
        $this->state = 'submit';
        $this->render();
        $this->restoreCursor();
        $this->resetTerminal();
    }

    /**
     * Get the completion percentage.
     */
    public function percentage(): int|float
    {
        return $this->progress / $this->total;
    }

    /**
     * Disable prompting for input.
     *
     * @throws \RuntimeException
     */
    public function prompt(): never
    {
        throw new RuntimeException('Progress Bar cannot be prompted.');
    }

    /**
     * Get the value of the prompt.
     */
    public function value(): bool
    {
        return true;
    }

    /**
     * Reset the terminal.
     */
    public function resetTerminal(): void
    {
        if (isset($this->originalAsync)) {
            pcntl_async_signals($this->originalAsync);
            pcntl_signal(SIGINT, SIG_DFL);
        }
    }

    /**
     * Restore the cursor.
     */
    public function __destruct()
    {
        $this->restoreCursor();
    }
}
