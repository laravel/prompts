<?php

namespace Laravel\Prompts;

use Closure;

class PasswordPrompt extends Prompt
{
    use Concerns\Colors;
    use Concerns\TypedValue;

    /**
     * Create a new PasswordPrompt instance.
     */
    public function __construct(
        public string $message,
        protected ?Closure $validate = null,
        public string $mask = '•',
    ) {
        $this->trackTypedValue();
    }

    /**
     * Get a masked version of the entered value.
     */
    public function masked(): string {
        return $this->value() ? str_repeat($this->mask, strlen($this->value())) : '';
    }

    /**
     * Get the masked value with a virtual cursor.
     */
    public function maskedWithCursor(): string
    {
        if ($this->cursorPosition >= strlen($this->value())) {
            return $this->masked() . $this->inverse($this->hidden('_'));
        }

        return mb_substr($this->masked(), 0, $this->cursorPosition)
            . $this->inverse(mb_substr($this->masked(), $this->cursorPosition, 1))
            . mb_substr($this->masked(), $this->cursorPosition + 1);
    }
}
