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
    protected $typedValue;

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
        $this->typedValue = $default;

        if ($this->typedValue) {
            $this->cursorPosition = strlen($this->typedValue);
        }

        $this->on('key', function ($key) {
            if ($key[0] === "\e") {
                match ($key) {
                    Key::LEFT => $this->cursorPosition = max(0, $this->cursorPosition - 1),
                    Key::RIGHT => $this->cursorPosition = min(strlen($this->typedValue), $this->cursorPosition + 1),
                    Key::DELETE => $this->typedValue = substr($this->typedValue, 0, $this->cursorPosition) . substr($this->typedValue, $this->cursorPosition + 1),
                    default => null,
                };
            } elseif ($key === key::BACKSPACE) {
                $this->typedValue = substr($this->typedValue, 0, $this->cursorPosition - 1) . substr($this->typedValue, $this->cursorPosition);
                $this->cursorPosition = max(0, $this->cursorPosition - 1);
            } elseif ($key !== key::ENTER && $key !== key::CTRL_C) {
                $this->typedValue = substr($this->typedValue, 0, $this->cursorPosition) . $key . substr($this->typedValue, $this->cursorPosition);
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
        return $this->typedValue;
    }
}
