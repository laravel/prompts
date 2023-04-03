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
    protected function trackTypedValue(string $default = ''): void
    {
        $this->typedValue = $default;

        if ($this->typedValue) {
            $this->cursorPosition = strlen($this->typedValue);
        }

        $this->on('key', function ($key) {
            if ($key[0] === "\e") {
                match ($key) {
                    Key::LEFT => $this->cursorPosition = max(0, $this->cursorPosition - 1),
                    Key::RIGHT => $this->cursorPosition = min(strlen($this->typedValue), $this->cursorPosition + 1),
                    Key::DELETE => $this->typedValue = substr($this->typedValue, 0, $this->cursorPosition).substr($this->typedValue, $this->cursorPosition + 1),
                    default => null,
                };
            } elseif ($key === Key::BACKSPACE) {
                $this->typedValue = substr($this->typedValue, 0, $this->cursorPosition - 1).substr($this->typedValue, $this->cursorPosition);
                $this->cursorPosition = max(0, $this->cursorPosition - 1);
            } elseif (!in_array($key, $this->ignore)) {
                $this->typedValue = substr($this->typedValue, 0, $this->cursorPosition).$key.substr($this->typedValue, $this->cursorPosition);
                $this->cursorPosition++;
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
}
