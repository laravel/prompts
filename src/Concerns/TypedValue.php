<?php

namespace Laravel\Prompts\Concerns;

use Laravel\Prompts\Key;

trait TypedValue
{
    /**
     * The value that has been typed.
     *
     * @var string|null
     */
    protected $value;

    /**
     * The position of the virtual cursor.
     *
     * @var int
     */
    protected $cursorPosition = 0;

    /**
     * Track the value as the user types.
     *
     * @param  string|null  $default
     * @return void
     */
    protected function trackTypedValue($default = null)
    {
        $this->value = $default;

        if ($this->value) {
            $this->cursorPosition = strlen($this->value);
        }

        $this->on('key', function ($key) {
            if ($key[0] === "\e") {
                match ($key) {
                    Key::LEFT => $this->cursorPosition = max(0, $this->cursorPosition - 1),
                    Key::RIGHT => $this->cursorPosition = min(strlen($this->value), $this->cursorPosition + 1),
                    Key::DELETE => $this->value = substr($this->value, 0, $this->cursorPosition) . substr($this->value, $this->cursorPosition + 1),
                    default => null,
                };
            } elseif ($key === key::BACKSPACE) {
                $this->value = substr($this->value, 0, $this->cursorPosition - 1) . substr($this->value, $this->cursorPosition);
                $this->cursorPosition = max(0, $this->cursorPosition - 1);
            } elseif ($key !== key::ENTER) {
                $this->value = substr($this->value, 0, $this->cursorPosition) . $key . substr($this->value, $this->cursorPosition);
                $this->cursorPosition++;
            }
        });
    }

    /**
     * Get the value of the prompt.
     *
     * @return string|null
     */
    public function value()
    {
        return $this->value;
    }
}
