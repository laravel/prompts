<?php

namespace Laravel\Prompts;

class SelectPrompt extends Prompt
{
    /**
     * The index of the highlighted option.
     *
     * @var int
     */
    public $highlighted = 0;

    /**
     * Create a new SelectPrompt instance.
     *
     * @param  string  $message
     * @param  array<int|string, string>  $options
     * @param  int|string  $default
     * @return void
     */
    public function __construct(
        public $message,
        public $options,
        protected $default = null,
    ) {
        if ($this->default) {
            if (array_is_list($this->options)) {
                $this->highlighted = array_search($this->default, $this->options);
            } else {
                $this->highlighted = array_search($this->default, array_keys($this->options));
            }
        }

        $this->on('key', fn ($key) => match ($key) {
            Key::UP, Key::LEFT, 'k', 'h' => $this->highlightPrevious(),
            Key::DOWN, Key::RIGHT, 'j', 'l' => $this->highlightNext(),
            default => null,
        });
    }

    /**
     * Get the selected value.
     *
     * @return string
     */
    public function value()
    {
        if (array_is_list($this->options)) {
            return $this->options[$this->highlighted] ?? null;
        } else {
            return array_keys($this->options)[$this->highlighted];
        }
    }

    /**
     * Get the selected label.
     *
     * @return string|int
     */
    public function label()
    {
        if (array_is_list($this->options)) {
            return $this->options[$this->highlighted] ?? null;
        } else {
            return $this->options[array_keys($this->options)[$this->highlighted]] ?? null;
        }
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
}
