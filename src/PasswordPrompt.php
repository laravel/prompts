<?php

namespace Laravel\Prompts;

use Closure;

class PasswordPrompt extends Prompt
{
    use Concerns\TypedValue;

    /**
     * Create a new PasswordPrompt instance.
     */
    public function __construct(
        public string $label,
        public bool|string $required = false,
        public ?Closure $validate = null,
    ) {
        $this->trackTypedValue();
    }

    /**
     * Get a masked version of the entered value.
     */
    public function masked(): string
    {
        return str_repeat('•', strlen($this->value()));
    }

    /**
     * Get the masked value with a virtual cursor.
     */
    public function maskedWithCursor(int $maxWidth): string
    {
        return $this->addCursor($this->masked(), $this->cursorPosition, $maxWidth);
    }
}
