<?php

namespace Laravel\Prompts;

use Closure;
use Laravel\Prompts\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

abstract class Prompt
{
    use Concerns\Cursor;
    use Concerns\Erase;
    use Concerns\Events;
    use Concerns\FakesInputOutput;
    use Concerns\Fallback;
    use Concerns\Themes;

    /**
     * The current state of the prompt.
     */
    public string $state = 'initial';

    /**
     * The error message from the validator.
     */
    public string $error = '';

    /**
     * The previously rendered frame.
     */
    protected string $prevFrame = '';

    /**
     * How many new lines were written by the last output.
     */
    protected int $newLinesWritten = 1;

    /**
     * Whether user input is required.
     */
    public bool|string $required;

    /**
     * The validator callback.
     */
    protected ?Closure $validate;

    /**
     * Indicates if the prompt has been validated.
     */
    protected bool $validated = false;

    /**
     * The output instance.
     */
    protected static OutputInterface $output;

    /**
     * The terminal instance.
     */
    protected static Terminal $terminal;

    /**
     * Get the value of the prompt.
     */
    abstract public function value(): mixed;

    /**
     * Render the prompt and listen for input.
     */
    public function prompt(): mixed
    {
        $this->capturePreviousNewLines();

        if ($this->shouldFallback()) {
            return $this->fallback();
        }

        register_shutdown_function(function () {
            $this->restoreCursor();
            $this->terminal()->restoreTty();
        });

        $this->terminal()->setTty('-icanon -isig -echo');
        $this->hideCursor();
        $this->render();

        while ($key = $this->terminal()->read()) {
            $continue = $this->handleKeyPress($key);

            $this->render();

            if ($continue === false || $key === Key::CTRL_C) {
                $this->restoreCursor();
                $this->terminal()->restoreTty();

                if ($key === Key::CTRL_C) {
                    $this->terminal()->exit();
                }

                return $this->value();
            }
        }

        return $this->value();
    }

    /**
     * How many new lines were written by the last output.
     */
    public function newLinesWritten(): int
    {
        return $this->newLinesWritten;
    }

    /**
     * Capture the number of new lines written by the last output.
     */
    protected function capturePreviousNewLines(): void
    {
        $this->newLinesWritten = method_exists($this->output(), 'newLinesWritten')
            ? $this->output()->newLinesWritten()
            : 1;
    }

    /**
     * Set the output instance.
     */
    public static function setOutput(OutputInterface $output): void
    {
        self::$output = $output;
    }

    /**
     * Get the current output instance.
     */
    protected static function output(): OutputInterface
    {
        return self::$output ??= new ConsoleOutput();
    }

    /**
     * Set or get the terminal instance.
     */
    protected static function terminal(Terminal $terminal = null): Terminal
    {
        if ($terminal) {
            return static::$terminal = $terminal;
        }

        return static::$terminal ??= new Terminal();
    }

    /**
     * Render the prompt.
     */
    protected function render(): void
    {
        $frame = $this->renderTheme();

        if ($frame === $this->prevFrame) {
            return;
        }

        if ($this->state === 'initial') {
            $this->output()->write($frame);

            $this->state = 'active';
            $this->prevFrame = $frame;

            return;
        }

        $this->resetCursorPosition();

        // Ensure that the full frame is buffered so subsequent output can see how many trailing newlines were written.
        if ($this->state === 'submit') {
            $this->eraseDown();
            $this->output()->write($frame);

            $this->prevFrame = '';

            return;
        }

        $diff = $this->diffLines($this->prevFrame, $frame);

        if (count($diff) === 1) { // Update the single line that changed.
            $diffLine = $diff[0];
            $this->moveCursor(0, $diffLine);
            $this->eraseLines(1);
            $lines = explode(PHP_EOL, $frame);
            $this->output()->write($lines[$diffLine]);
            $this->moveCursor(0, count($lines) - $diffLine - 1);
        } elseif (count($diff) > 1) { // Re-render everything past the first change
            $diffLine = $diff[0];
            $this->moveCursor(0, $diffLine);
            $this->eraseDown();
            $lines = explode(PHP_EOL, $frame);
            $newLines = array_slice($lines, $diffLine);
            $this->output()->write(implode(PHP_EOL, $newLines));
        }

        $this->prevFrame = $frame;
    }

    /**
     * Reset the cursor position to the beginning of the previous frame.
     */
    private function resetCursorPosition(): void
    {
        $lines = count(explode(PHP_EOL, $this->prevFrame)) - 1;

        $this->moveCursor(-999, $lines * -1);
    }

    /**
     * Get the difference between two strings.
     *
     * @return array<int>
     */
    private function diffLines(string $a, string $b): array
    {
        if ($a === $b) {
            return [];
        }

        $aLines = explode(PHP_EOL, $a);
        $bLines = explode(PHP_EOL, $b);
        $diff = [];

        for ($i = 0; $i < max(count($aLines), count($bLines)); $i++) {
            if (! isset($aLines[$i]) || ! isset($bLines[$i]) || $aLines[$i] !== $bLines[$i]) {
                $diff[] = $i;
            }
        }

        return $diff;
    }

    /**
     * Handle a key press and determine whether to continue.
     */
    private function handleKeyPress(string $key): bool
    {
        if ($this->state === 'error') {
            $this->state = 'active';
        }

        $this->emit('key', $key);

        if ($key === Key::CTRL_C) {
            $this->state = 'cancel';
        } elseif ($key === Key::ENTER || $this->validated) {
            $this->error = $this->validate();
            $this->validated = true;

            if ($this->error) {
                $this->state = 'error';
            } elseif ($key === Key::ENTER) {
                $this->state = 'submit';
            }
        }

        if ($this->state === 'submit' || $this->state === 'cancel') {
            return false;
        }

        return true;
    }

    /**
     * Validate the input.
     */
    private function validate(): string
    {
        if (($this->required ?? false) && empty($this->value())) {
            return is_string($this->required) ? $this->required : 'Required.';
        }

        if (! isset($this->validate)) {
            return '';
        }

        $error = ($this->validate)($this->value());

        if (! is_string($error) && ! is_null($error)) {
            throw new \RuntimeException('The validator must return a string or null.');
        }

        return $error ?? '';
    }
}
