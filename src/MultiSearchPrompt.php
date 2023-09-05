<?php

namespace Laravel\Prompts;

use Closure;
use Illuminate\Support\Collection;

class MultiSearchPrompt extends Prompt
{
    use Concerns\Truncation;
    use Concerns\TypedValue;

    /**
     * The index of the highlighted option.
     */
    public ?int $highlighted = null;

    /**
     * The index of the first visible option.
     */
    public int $firstVisible = 0;

    /**
     * The cached matches.
     *
     * @var array<int|string, string>|null
     */
    protected ?array $matches = null;

    /**
     * The default values the multi-search prompt.
     *
     * @var array<int|string, string>
     */
    public array $default;

    /**
     * The selected values.
     *
     * @var array<int|string, string>
     */
    public array $values = [];

    /**
     * Create a new MultiSearchPrompt instance.
     *
     * @param  array<int|string, string>|Collection<int|string, string>  $default
     * @param  Closure(string): array<int|string, string>  $options
     */
    public function __construct(
        public string $label,
        public Closure $options,
        public bool $returnKeys = true,
        array|Collection $default = [],
        public string $placeholder = '',
        public int $scroll = 5,
        public bool|string $required = false,
        public ?Closure $validate = null,
        public string $hint = '',
    ) {
        $this->default = $default instanceof Collection ? $default->all() : $default;
        $this->values = $this->default;

        $this->trackTypedValue(submit: false, allowKey: fn ($key) => $key !== Key::SPACE || $this->highlighted === null);

        $this->on('key', fn ($key) => match ($key) {
            Key::UP, Key::UP_ARROW, Key::SHIFT_TAB => $this->highlightPrevious(),
            Key::DOWN, Key::DOWN_ARROW, Key::TAB => $this->highlightNext(),
            Key::SPACE => $this->highlighted !== null ? $this->toggleHighlighted() : null,
            Key::ENTER => $this->submit(),
            Key::LEFT, Key::LEFT_ARROW, Key::RIGHT, Key::RIGHT_ARROW => $this->highlighted = null,
            default => $this->search(),
        });
    }

    /**
     * Perform the search.
     */
    protected function search(): void
    {
        $this->state = 'searching';
        $this->highlighted = null;
        $this->render();
        $this->matches = null;
        $this->firstVisible = 0;
        $this->state = 'active';
    }

    /**
     * Get the entered value with a virtual cursor.
     */
    public function valueWithCursor(int $maxWidth): string
    {
        if ($this->highlighted !== null) {
            return $this->typedValue === ''
                ? $this->dim($this->truncate($this->placeholder, $maxWidth))
                : $this->truncate($this->typedValue, $maxWidth);
        }

        if ($this->typedValue === '') {
            return $this->dim($this->addCursor($this->placeholder, 0, $maxWidth));
        }

        return $this->addCursor($this->typedValue, $this->cursorPosition, $maxWidth);
    }

    /**
     * Get options that match the input.
     *
     * @return array<string>
     */
    public function matches(): array
    {
        if (is_array($this->matches)) {
            return $this->matches;
        }

        if (strlen($this->typedValue) === 0) {
            return $this->matches = $this->values;
        }

        return $this->matches = ($this->options)($this->typedValue);
    }

    /**
     * The currently visible matches
     *
     * @return array<string>
     */
    public function visible(): array
    {
        return array_slice($this->matches(), $this->firstVisible, $this->scroll, preserve_keys: true);
    }

    /**
     * Highlight the previous entry, or wrap around to the last entry.
     */
    protected function highlightPrevious(): void
    {
        if ($this->matches === []) {
            $this->highlighted = null;
        } elseif ($this->highlighted === null) {
            $this->highlighted = count($this->matches) - 1;
        } elseif ($this->highlighted === 0) {
            $this->highlighted = null;
        } else {
            $this->highlighted = $this->highlighted - 1;
        }

        if ($this->highlighted < $this->firstVisible) {
            $this->firstVisible--;
        } elseif ($this->highlighted === count($this->matches) - 1) {
            $this->firstVisible = count($this->matches) - min($this->scroll, count($this->matches));
        }
    }

    /**
     * Highlight the next entry, or wrap around to the first entry.
     */
    protected function highlightNext(): void
    {
        if ($this->matches === []) {
            $this->highlighted = null;
        } elseif ($this->highlighted === null) {
            $this->highlighted = 0;
        } else {
            $this->highlighted = $this->highlighted === count($this->matches) - 1 ? null : $this->highlighted + 1;
        }

        if ($this->highlighted > $this->firstVisible + $this->scroll - 1) {
            $this->firstVisible++;
        } elseif ($this->highlighted === 0 || $this->highlighted === null) {
            $this->firstVisible = 0;
        }
    }

    /**
     * Toggle the highlighted entry.
     */
    protected function toggleHighlighted(): void
    {
        if ($this->returnKeys) {
            $key = array_keys($this->matches)[$this->highlighted];

            if (array_key_exists($key, $this->values)) {
                unset($this->values[$key]);
            } else {
                $this->values[$key] = $this->matches[$key];
            }
        } else {
            $value = $this->matches[$this->highlighted];

            if (in_array($value, $this->values)) {
                $this->values = array_filter($this->values, fn ($v) => $v !== $value);
            } else {
                $this->values[] = $value;
            }
        }
    }

    /**
     * Get the current search query.
     */
    public function searchValue(): string
    {
        return $this->typedValue;
    }

    /**
     * Get the selected value.
     *
     * @return array<int|string>
     */
    public function value(): array
    {
        return $this->returnKeys
            ? array_keys($this->values)
            : $this->values;
    }

    /**
     * Get the selected labels.
     *
     * @return array<string>
     */
    public function labels(): array
    {
        return array_values($this->values);
    }
}
