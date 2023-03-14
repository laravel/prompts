<?php

namespace Laravel\Prompts;

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
     *
     * @var string
     */
    public $state = 'initial';

    /**
     * The error message from the validator.
     *
     * @var string
     */
    public $error = '';

    /**
     * The previously rendered frame.
     *
     * @var string
     */
    protected string $prevFrame = '';

    /**
     * The validator callback.
     *
     * @var \Closure|null
     */
    protected $validate;

    /**
     * Indicates if the prompt has been validated.
     *
     * @var bool
     */
    protected $validated = false;

    /**
     * Get the value of the prompt.
     *
     * @return mixed
     */
    abstract public function value();

    /**
     * Render the prompt and listen for input.
     *
     * @return mixed
     */
    public function prompt()
    {
        $this->setTty('-icanon -isig -echo');

        try {
            $this->hideCursor();
            $this->render();

            while ($key = fread(STDIN, 1024)) {
                if ($key === Key::CTRL_C) {
                    $this->state = 'cancel';
                    $this->emit('cancel');
                    $this->render();
                    $this->showCursor();
                    $this->restoreTty();

                    exit(1);
                }

                $ret = $this->onKeypress($key);

                if ($ret === false) {
                    $this->showCursor();
                    $this->restoreTty();

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
     * Handle key presses.
     *
     * @param  string  $key
     * @return void
     */
    protected function onKeypress(string $key)
    {
        if ($this->state === 'error') {
            $this->state = 'active';
        }

        if (in_array($key, [Key::UP, Key::DOWN, Key::LEFT, Key::RIGHT])) {
            $this->emit('cursor', $key);
        }

        if ($key) {
            $this->emit('key', $key);
        }

        if ($key === Key::ENTER) {
            if ($this->validate) {
                $error = ($this->validate)($this->value());
                $this->validated = true;
                if ($error) {
                    $this->error = $error;
                    $this->state = 'error';
                }
            }
            if ($this->state !== 'error') {
                $this->state = 'submit';
            }
        } elseif ($this->validated) {
            $error = ($this->validate)($this->value());
            $this->validated = true;
            if ($error) {
                $this->error = $error;
                $this->state = 'error';
            }
        }
        if ($key === Key::CTRL_C) {
            $this->state = 'cancel';
        }

        $this->render();

        if ($this->state === 'submit' || $this->state === 'cancel') {
            return false;
        }
    }

    /**
     * Render the prompt.
     *
     * @return void
     */
    protected function render()
    {
        $frame = $this->renderTheme();

        if ($frame === $this->prevFrame) {
            return;
        }

        if ($this->state === 'initial') {
            //
        } else {
            $diff = $this->diffLines($this->prevFrame, $frame);
            $this->restoreCursor();

            // If a single line has changed, only update that line
            if (count($diff) === 1) {
                $diffLine = $diff[0];
                $this->moveCursor(0, $diffLine);
                $this->eraseLines(1);
                $lines = explode(PHP_EOL, $frame);
                fwrite(STDOUT, $lines[$diffLine]);
                $this->prevFrame = $frame;
                $this->moveCursor(0, count($lines) - $diffLine - 1);
                return;
            } else if (count($diff) > 1) {
                // If many lines have changed, rerender everything past the first line
                $diffLine = $diff[0];
                $this->moveCursor(0, $diffLine);
                $this->eraseDown();
                $lines = explode(PHP_EOL, $frame);
                $newLines = array_slice($lines, $diffLine);
                fwrite(STDOUT, implode(PHP_EOL, $newLines));
                $this->prevFrame = $frame;
                return;
            }

            $this->eraseDown();
        }

        fwrite(STDOUT, $frame);

        if ($this->state === 'initial') {
            $this->state = 'active';
        }

        $this->prevFrame = $frame;
    }

    /**
     * Restore the cursor position.
     *
     * @return void
     */
    private function restoreCursor() {
        $lines = count(explode(PHP_EOL, $this->prevFrame)) - 1;

        $this->moveCursor(-999, $lines * -1);
    }

    /**
     * Get the difference between two strings.
     *
     * @param  string  $a
     * @param  string  $b
     * @return array
     */
    protected function diffLines(string $a, string $b): array {
        if ($a === $b) return [];

        $aLines = explode(PHP_EOL, $a);
        $bLines = explode(PHP_EOL, $b);
        $diff = [];

        for ($i = 0; $i < max(count($aLines), count($bLines)); $i++) {
            if (!isset($aLines[$i]) || !isset($bLines[$i]) || $aLines[$i] !== $bLines[$i]) {
                $diff[] = $i;
            }
        }

        return $diff;
    }
}
