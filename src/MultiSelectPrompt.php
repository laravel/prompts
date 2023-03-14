<?php

namespace Laravel\Prompts;

class MultiSelectPrompt extends Prompt
{
    /**
     * The index of the highlighted option.
     *
     * @var int
     */
    public $highlighted = 0;

    /**
     * The selected values.
     *
     * @var array<int, string>
     */
    public $values = [];

    /**
     * Create a new SelectPrompt instance.
     *
     * @param  string  $message
     * @param  array<int|string, string>  $options
     * @param  array<int, string>  $default
     * @param  Closure|null  $validate
     * @return void
     */
    public function __construct(
        public $message,
        public $options,
        protected $default = [],
        protected $validate = null,
    ) {
        $this->values = $this->default;

        $this->on('key', fn ($key) => match ($key) {
            KEY::UP, KEY::LEFT => $this->highlightPrevious(),
            KEY::DOWN, KEY::RIGHT => $this->highlightNext(),
            KEY::SPACE => $this->toggleHighlighted(),
            default => null,
        });
    }

    /**
     * Get the selected values.
     *
     * @return array<int, string>
     */
    public function value()
    {
        return $this->values;
    }

    /**
     * Highlight the previous entry, or wrap around to the last entry.
     *
     * @return void
     */
    protected function highlightPrevious()
    {
        $this->highlighted = $this->highlighted === 0 ? count($this->options) - 1 : $this->highlighted - 1;
    }

    /**
     * Highlight the next entry, or wrap around to the first entry.
     *
     * @return void
     */
    protected function highlightNext()
    {
        $this->highlighted = $this->highlighted === count($this->options) - 1 ? 0 : $this->highlighted + 1;
    }

    /**
     * Toggle the highlighted entry.
     *
     * @return void
     */
    protected function toggleHighlighted()
    {
        $value = array_keys($this->options)[$this->highlighted];

        if (in_array($value, $this->values)) {
            $this->values = array_filter($this->values, fn ($v) => $v !== $value);
        } else {
            $this->values[] = $value;
        }
    }
}
