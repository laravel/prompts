<?php

namespace Laravel\Prompts;

class TextPrompt extends Prompt
{
    use Concerns\Colors;
    use Concerns\TypedValue;

    /**
     * Create a new TextPrompt instance.
     *
     * @param  string  $message
     * @param  string|null  $placeholder
     * @param  string  $default
     * @param  \Closure|null  $validate
     * @return void
     */
    public function __construct(
        public $message,
        public $placeholder = null,
        $default = '',
        protected $validate = null,
    ) {
        $this->trackTypedValue($default);
    }

    /**
     * Get the entered value with a virtual cursor.
     *
     * @return string
     */
    public function valueWithCursor()
    {
        if (! $this->value() && $this->placeholder) {
            return $this->inverse(substr($this->placeholder, 0, 1)) . $this->dim(substr($this->placeholder, 1));
        }

        if ($this->cursorPosition >= strlen($this->value())) {
            return $this->value() . $this->inverse($this->hidden('_'));
        }

        return mb_substr($this->value(), 0, $this->cursorPosition)
            . $this->inverse(mb_substr($this->value(), $this->cursorPosition, 1))
            . mb_substr($this->value(), $this->cursorPosition + 1);
    }
}
