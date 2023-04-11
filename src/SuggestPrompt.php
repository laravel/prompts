<?php

namespace Laravel\Prompts;

use Closure;

class SuggestPrompt extends Prompt
{
    use Concerns\Colors;
    use Concerns\TypedValue;

    /**
     * The index of the highlighted option.
     */
    public int|null $highlighted = null;

    /**
     * Create a new SuggestPrompt instance.
     *
     * @param  array<string>|Closure(string): array<string>  $options
     */
    public function __construct(
        public string $label,
        public array|Closure $options,
        public string $placeholder = '',
        public string $default = '',
        public int $scroll = 5,
        public ?Closure $validate = null,
    ) {
        $this->trackTypedValue($default);

        $this->on('key', fn ($key) => match ($key) {
            Key::UP, Key::SHIFT_TAB => $this->highlightPrevious(),
            Key::DOWN, Key::TAB => $this->highlightNext(),
            Key::ENTER => $this->selectHighlighted(),
            default => $this->highlighted = null,
        });
    }

    /**
     * Get the entered value with a virtual cursor.
     */
    public function valueWithCursor(): string
    {
        if ($this->highlighted !== null) {
            return $this->value() ?: $this->dim($this->placeholder);
        }

        if (! $this->value() && $this->placeholder) {
            return $this->inverse(substr($this->placeholder, 0, 1)).$this->dim(substr($this->placeholder, 1));
        }

        if ($this->cursorPosition >= strlen($this->value())) {
            return $this->value().$this->inverse($this->hidden('_'));
        }

        return mb_substr($this->value(), 0, $this->cursorPosition)
            .$this->inverse(mb_substr($this->value(), $this->cursorPosition, 1))
            .mb_substr($this->value(), $this->cursorPosition + 1);
    }

    /**
     * Get options that match the input.
     *
     * @return array<string>
     */
    public function matches(): array
    {
        if ($this->options instanceof Closure) {
            return array_values(($this->options)($this->value()));
        }

        return array_values(array_filter($this->options, function ($option) {
            return str_starts_with(strtolower($option), strtolower($this->value()));
        }));
    }

    /**
     * Highlight the previous entry, or wrap around to the last entry.
     */
    protected function highlightPrevious(): void
    {
        if ($this->matches() === []) {
            $this->highlighted = null;
        } elseif ($this->highlighted === null) {
            $this->highlighted = count($this->matches()) - 1;
        } elseif ($this->highlighted === 0) {
            $this->highlighted = null;
        } else {
            $this->highlighted = $this->highlighted - 1;
        }
    }

    /**
     * Highlight the next entry, or wrap around to the first entry.
     */
    protected function highlightNext(): void
    {
        if ($this->matches() === []) {
            $this->highlighted = null;
        } elseif ($this->highlighted === null) {
            $this->highlighted = 0;
        } else {
            $this->highlighted = $this->highlighted === count($this->matches()) - 1 ? null : $this->highlighted + 1;
        }
    }

    /**
     * Select the highlighted entry.
     */
    protected function selectHighlighted(): void
    {
        if ($this->highlighted === null) {
            return;
        }

        $this->typedValue = $this->matches()[$this->highlighted];
    }
}
