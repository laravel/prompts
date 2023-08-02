<?php

namespace Laravel\Prompts\Concerns;

use Laravel\Prompts\Key;

trait TypedValue
{
    /**
     * The value that has been typed.
     */
    protected string $typedValue = '';

    /**
     * The position of the virtual cursor.
     */
    protected int $cursorPosition = 0;

    /**
     * Keys to ignore
     *
     * @var array<string>
     */
    protected array $ignore = [
        Key::ENTER,
        Key::TAB,
        Key::CTRL_C,
        Key::CTRL_D,
    ];

    /**
     * Track the value as the user types.
     */
    protected function trackTypedValue(string $default = '', bool $submit = true): void
    {
        $this->typedValue = $default;

        if ($this->typedValue) {
            $this->cursorPosition = mb_strlen($this->typedValue);
        }

        $this->on('key', function ($key) use ($submit) {
            if ($key[0] === "\e") {
                match ($key) {
                    Key::LEFT => $this->cursorPosition = max(0, $this->cursorPosition - 1),
                    Key::RIGHT => $this->cursorPosition = min(mb_strlen($this->typedValue), $this->cursorPosition + 1),
                    Key::DELETE => $this->typedValue = mb_substr($this->typedValue, 0, $this->cursorPosition).mb_substr($this->typedValue, $this->cursorPosition + 1),
                    default => null,
                };

                return;
            }

            // Keys may be buffered.
            foreach (mb_str_split($key) as $key) {
                if ($key === Key::ENTER && $submit) {
                    $this->submit();

                    return;
                } elseif ($key === Key::BACKSPACE) {
                    if ($this->cursorPosition === 0) {
                        return;
                    }

                    $this->typedValue = mb_substr($this->typedValue, 0, $this->cursorPosition - 1).mb_substr($this->typedValue, $this->cursorPosition);
                    $this->cursorPosition--;
                } elseif (! in_array($key, $this->ignore)) {
                    $this->typedValue = mb_substr($this->typedValue, 0, $this->cursorPosition).$key.mb_substr($this->typedValue, $this->cursorPosition);
                    $this->cursorPosition++;
                }
            }
        });
    }

    /**
     * Get the value of the prompt.
     */
    public function value(): string
    {
        return $this->typedValue;
    }

    /**
     * Add a virtual cursor to the value and truncate if necessary.
     */
    protected function addCursor(string $value, int $cursorPosition, int $maxWidth): string
    {
        $offset = $cursorPosition - $maxWidth + ($cursorPosition < mb_strlen($value) ? 2 : 1);
        $offset = $offset > 0 ? $offset + 1 : 0;
        $offsetCursorPosition = $cursorPosition - $offset;

        $output = $offset > 0 ? $this->dim('…') : '';
        $output .= mb_substr($value, $offset, $offsetCursorPosition);

        if ($cursorPosition > mb_strlen($value) - 1) {
            return $output.$this->inverse(' ');
        }

        $output .= $this->inverse(mb_substr($value, $cursorPosition, 1));

        if ($cursorPosition === mb_strlen($value) - 1) {
            return $output.' ';
        }

        $remainder = mb_substr($value, $cursorPosition + 1);
        $remainingSpace = $maxWidth - $offsetCursorPosition - ($offset ? 2 : 1);

        if (mb_strlen($remainder) <= $remainingSpace) {
            return $output.$remainder;
        }

        return $output.mb_substr($remainder, 0, $remainingSpace - 1).$this->dim('…');
    }
}
