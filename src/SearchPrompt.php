<?php

namespace Laravel\Prompts;

use Closure;

class SearchPrompt extends Prompt
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
     * Whether user input is required.
     */
    public bool|string $required = true;

    /**
     * The cached matches.
     *
     * @var array<int|string, string>|null
     */
    protected ?array $matches = null;

    /**
     * Create a new SearchPrompt instance.
     *
     * @param  Closure(string): array<int|string, string>  $options
     */
    public function __construct(
        public string $label,
        public Closure $options,
        public string $placeholder = '',
        public int $scroll = 5,
        public ?Closure $validate = null,
        public string $hint = ''
    ) {
        $this->trackTypedValue(submit: false);

        $this->reduceScrollingToFitTerminal();

        $this->on('key', fn ($key) => match ($key) {
            Key::UP, Key::UP_ARROW, Key::SHIFT_TAB, Key::CTRL_P => $this->highlightOffset(-1),
            Key::DOWN, Key::DOWN_ARROW, Key::TAB, Key::CTRL_N => $this->highlightOffset(1),
            Key::HOME, Key::CTRL_A => $this->highlighted !== null ? $this->highlight(0) : null,
            Key::END, Key::CTRL_E => $this->highlighted !== null ? $this->highlight(count($this->matches()) - 1) : null,
            Key::ENTER => $this->highlighted !== null ? $this->submit() : $this->search(),
            Key::LEFT, Key::LEFT_ARROW, Key::RIGHT, Key::RIGHT_ARROW, Key::CTRL_B, Key::CTRL_F => $this->highlighted = null,
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

        return $this->matches = ($this->options)($this->typedValue);
    }

    /**
     * The currently visible matches.
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
     * Get the current search query.
     */
    public function searchValue(): string
    {
        return $this->typedValue;
    }

    /**
     * Get the selected value.
     */
    public function value(): int|string|null
    {
        if ($this->matches === null || $this->highlighted === null) {
            return null;
        }

        return array_is_list($this->matches)
            ? $this->matches[$this->highlighted]
            : array_keys($this->matches)[$this->highlighted];
    }

    /**
     * Get the selected label.
     */
    public function label(): ?string
    {
        return $this->matches[array_keys($this->matches)[$this->highlighted]] ?? null;
    }
}
