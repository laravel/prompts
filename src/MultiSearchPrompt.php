<?php

namespace Laravel\Prompts;

use Closure;

class MultiSearchPrompt extends Prompt
{
    use Concerns\ReducesScrollingToFitTerminal;
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
     * The selected values.
     *
     * @var array<int|string, string>
     */
    public array $values = [];

    /**
     * Create a new MultiSearchPrompt instance.
     *
     * @param  Closure(string): array<int|string, string>  $options
     */
    public function __construct(
        public string $label,
        public Closure $options,
        public string $placeholder = '',
        public int $scroll = 5,
        public bool|string $required = false,
        public ?Closure $validate = null,
        public string $hint = '',
    ) {
        $this->trackTypedValue(submit: false, ignore: fn ($key) => $key === Key::SPACE && $this->highlighted !== null);

        $this->reduceScrollingToFitTerminal();

        $this->on('key', fn ($key) => match ($key) {
            Key::UP, Key::UP_ARROW, Key::SHIFT_TAB => $this->highlightOffset(-1),
            Key::DOWN, Key::DOWN_ARROW, Key::TAB => $this->highlightOffset(1),
            Key::HOME, Key::CTRL_A => $this->highlighted !== null ? $this->highlight(0) : null,
            Key::END, Key::CTRL_E => $this->highlighted !== null ? $this->highlight(count($this->matches()) - 1) : null,
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
            $matches = ($this->options)($this->typedValue);

            return $this->matches = [
                ...array_diff($this->values, $matches),
                ...$matches,
            ];
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

    protected function highlightOffset(int $offset): void
    {
        if ($offset === 0 || $this->matches === []) {
            return;
        }

        if (count($this->matches) === 1) {
            $this->highlighted = $this->highlighted === null ? 0 : null;
        } elseif ($this->highlighted === 0 && $offset < 0) {
            $this->highlighted = null;
        } elseif ($this->highlighted === null) {
            $this->highlighted = $offset < 0 ? count($this->matches) - 1 : 0;
        } elseif ($this->highlighted === count($this->matches) - 1 && $offset > 0) {
            $this->highlighted = null;
        } else {
            $this->highlighted = $offset < 0 ? max(0, $this->highlighted + $offset) : min(count($this->matches) - 1, $this->highlighted + $offset);
        }

        $this->updateFirstVisible();
    }

    protected function highlight(?int $highlight): void
    {
        $this->highlighted = $highlight;
        $this->updateFirstVisible();
    }

    protected function updateFirstVisible(): void
    {
        if ($this->highlighted === null) {
            return;
        }

        if ($this->highlighted < $this->firstVisible) {
            $this->firstVisible = $this->highlighted;
        } elseif ($this->highlighted > $this->firstVisible + $this->scroll - 1) {
            $this->firstVisible = $this->highlighted - $this->scroll + 1;
        }
    }

    /**
     * Toggle the highlighted entry.
     */
    protected function toggleHighlighted(): void
    {
        if (array_is_list($this->matches)) {
            $label = $this->matches[$this->highlighted];
            $key = $label;
        } else {
            $key = array_keys($this->matches)[$this->highlighted];
            $label = $this->matches[$key];
        }

        if (array_key_exists($key, $this->values)) {
            unset($this->values[$key]);
        } else {
            $this->values[$key] = $label;
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
        return array_keys($this->values);
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
