<?php

namespace Laravel\Prompts;

use Closure;
use Throwable;

abstract class Prompt
{
    use Concerns\Cursor;
    use Concerns\Erase;
    use Concerns\Events;
    use Concerns\Themes;
    use Concerns\Tty;

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
     * The validator callback.
     */
    protected ?Closure $validate;

    /**
     * Indicates if the prompt has been validated.
     */
    protected bool $validated = false;

    /**
     * Get the value of the prompt.
     */
    abstract public function value(): mixed;

    /**
     * Render the prompt and listen for input.
     */
    public function prompt(): mixed
    {
        try {
            $this->setTty('-icanon -isig -echo');
            $this->hideCursor();
            $this->render();

            while ($key = fread(STDIN, 1024)) {
                $continue = $this->handleKeyPress($key);

                $this->render();

                if ($continue === false || $key === Key::CTRL_C) {
                    $this->showCursor();
                    $this->restoreTty();

                    if ($key === Key::CTRL_C) {
                        exit(1);
                    }

                    return $this->value();
                }
            }
        } catch (Throwable $e) {
            $this->showCursor();
            $this->restoreTty();

            throw $e;
        }
    }

    /**
     * Handle a key press.
     */
    protected function handleKeyPress(string $key): ?bool
    {
        if ($this->state === 'error') {
            $this->state = 'active';
        }

        $this->emit('key', $key);

        if ($key === Key::ENTER || $this->validated) {
            $this->error = $this->validate();
            $this->validated = true;

            if ($this->error) {
                $this->state = 'error';
            } elseif ($key === Key::ENTER) {
                $this->state = 'submit';
            }
        } elseif ($key === Key::CTRL_C) {
            $this->state = 'cancel';
        }

        if ($this->state === 'submit' || $this->state === 'cancel') {
            return false;
        }

        return null;
    }

    /**
     * Validate the input.
     */
    protected function validate(): string
    {
        if (! isset($this->validate)) {
            return '';
        }

        $error = ($this->validate)($this->value());

        if (! is_string($error) && ! is_null($error)) {
            throw new \RuntimeException('The validator must return a string or null.');
        }

        return $error ?? '';
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
            fwrite(STDOUT, $frame);

            $this->state = 'active';
            $this->prevFrame = $frame;

            return;
        }

        $this->restoreCursor();

        $diff = $this->diffLines($this->prevFrame, $frame);

        if (count($diff) === 1) { // Update the single line that changed.
            $diffLine = $diff[0];
            $this->moveCursor(0, $diffLine);
            $this->eraseLines(1);
            $lines = explode(PHP_EOL, $frame);
            fwrite(STDOUT, $lines[$diffLine]);
            $this->moveCursor(0, count($lines) - $diffLine - 1);
        } elseif (count($diff) > 1) { // Re-render everything past the first change
            $diffLine = $diff[0];
            $this->moveCursor(0, $diffLine);
            $this->eraseDown();
            $lines = explode(PHP_EOL, $frame);
            $newLines = array_slice($lines, $diffLine);
            fwrite(STDOUT, implode(PHP_EOL, $newLines));
        }

        $this->prevFrame = $frame;
    }

    /**
     * Restore the cursor position.
     */
    private function restoreCursor(): void
    {
        $lines = count(explode(PHP_EOL, $this->prevFrame)) - 1;

        $this->moveCursor(-999, $lines * -1);
    }

    /**
     * Get the difference between two strings.
     *
     * @return array<int>
     */
    protected function diffLines(string $a, string $b): array
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
}
