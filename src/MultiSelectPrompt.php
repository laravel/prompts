<?php

namespace Laravel\Prompts;

use Closure;

class MultiSelectPrompt extends Prompt
{
    /**
     * The index of the highlighted option.
     */
    public int $highlighted = 0;

    /**
     * The selected values.
     *
     * @var array<string>
     */
    public array $values = [];

    /**
     * Create a new SelectPrompt instance.
     *
     * @param array<int|string, string>  $options
     * @param array<string>  $default
     */
    public function __construct(
        public string $message,
        public array $options,
        protected array $default = [],
        protected ?Closure $validate = null,
    ) {
        $this->values = $this->default;

        $this->on('key', fn ($key) => match ($key) {
            Key::UP, Key::LEFT, 'k', 'h' => $this->highlightPrevious(),
            Key::DOWN, Key::RIGHT, 'j', 'l' => $this->highlightNext(),
            Key::SPACE => $this->toggleHighlighted(),
            default => null,
        });
    }

    /**
     * Get the selected values.
     *
     * @return array<string>
     */
    public function value(): array
    {
        return $this->values;
    }

    /**
     * Highlight the previous entry, or wrap around to the last entry.
     */
    protected function highlightPrevious(): void
    {
        $this->highlighted = $this->highlighted === 0 ? count($this->options) - 1 : $this->highlighted - 1;
    }

    /**
     * Highlight the next entry, or wrap around to the first entry.
     */
    protected function highlightNext(): void
    {
        $this->highlighted = $this->highlighted === count($this->options) - 1 ? 0 : $this->highlighted + 1;
    }

    /**
     * Toggle the highlighted entry.
     */
    protected function toggleHighlighted(): void
    {
        $value = array_keys($this->options)[$this->highlighted];

        if (in_array($value, $this->values)) {
            $this->values = array_filter($this->values, fn ($v) => $v !== $value);
        } else {
            $this->values[] = $value;
        }
    }
}
