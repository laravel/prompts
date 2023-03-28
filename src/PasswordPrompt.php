<?php

namespace Laravel\Prompts;

class PasswordPrompt extends Prompt
{
    use Concerns\Colors;
    use Concerns\TypedValue;

    /**
     * Create a new PasswordPrompt instance.
     *
     * @param  string  $message
     * @param  \Closure|null  $validate
     * @param  string  $mask
     * @return void
     */
    public function __construct(
        public $message,
        protected $validate = null,
        public $mask = '•',
    ) {
        $this->trackTypedValue();
    }

    /**
     * Get a masked version of the entered value.
     *
     * @return string
     */
    public function masked() {
        return $this->value() ? str_repeat($this->mask, strlen($this->value())) : '';
    }

    /**
     * Get the masked value with a virtual cursor.
     *
     * @return string
     */
    public function maskedWithCursor()
    {
        if ($this->cursorPosition >= strlen($this->value())) {
            return $this->masked() . $this->inverse($this->hidden('_'));
        }

        return mb_substr($this->masked(), 0, $this->cursorPosition)
            . $this->inverse(mb_substr($this->masked(), $this->cursorPosition, 1))
            . mb_substr($this->masked(), $this->cursorPosition + 1);
    }
}
