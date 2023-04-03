<?php

namespace Laravel\Prompts;

class SelectPrompt extends Prompt
{
    use Concerns\Scroll;

    /**
     * The index of the highlighted option.
     */
    public int $highlighted = 0;

    /**
     * Create a new SelectPrompt instance.
     *
     * @param  array<int|string, string>  $options
     */
    public function __construct(
        public string $message,
        public array $options,
        protected int|string|null $default = null,
        public int|bool $scroll = 5,
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
     */
    public function value(): string
    {
        if (array_is_list($this->options)) {
            return $this->options[$this->highlighted] ?? null;
        } else {
            return array_keys($this->options)[$this->highlighted];
        }
    }

    /**
     * Get the selected label.
     */
    public function label(): string
    {
        if (array_is_list($this->options)) {
            return $this->options[$this->highlighted] ?? null;
        } else {
            return $this->options[array_keys($this->options)[$this->highlighted]] ?? null;
        }
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
     * Get a scrolled version of the labels.
     *
     * @return array<string>
     */
    public function scrolledLabels(): array
    {
        return $this->scrolled(array_values($this->options), $this->highlighted);
    }

    /**
     * Return whether there are labels above the current scroll position.
     */
    public function hasLabelsAbove(): bool
    {
        return $this->hasItemsAbove($this->options, $this->highlighted);
    }

    /**
     * Return whether there are labels below the current scroll position.
     */
    public function hasLabelsBelow(): bool
    {
        return $this->hasItemsBelow($this->options, $this->highlighted);
    }
}
