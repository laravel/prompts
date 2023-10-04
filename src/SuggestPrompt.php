<?php

namespace Laravel\Prompts;

use Closure;
use Illuminate\Support\Collection;

class SuggestPrompt extends Prompt
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
     * The options for the suggest prompt.
     *
     * @var array<string>|Closure(string): array<string>
     */
    public array|Closure $options;

    /**
     * The cache of matches.
     *
     * @var array<string>|null
     */
    protected ?array $matches = null;

    /**
     * Create a new SuggestPrompt instance.
     *
     * @param  array<string>|Collection<int, string>|Closure(string): array<string>  $options
     */
    public function __construct(
        public string $label,
        array|Collection|Closure $options,
        public string $placeholder = '',
        public string $default = '',
        public int $scroll = 5,
        public bool|string $required = false,
        public ?Closure $validate = null,
        public string $hint = ''
    ) {
        $this->options = $options instanceof Collection ? $options->all() : $options;

        $this->reduceScrollingToFitTerminal();

        $this->on('key', fn ($key) => match ($key) {
            Key::UP, Key::UP_ARROW, Key::SHIFT_TAB, Key::CTRL_P => $this->highlightOffset(-1),
            Key::DOWN, Key::DOWN_ARROW, Key::TAB, Key::CTRL_N => $this->highlightOffset(1),
            Key::oneOf([Key::HOME, Key::CTRL_A], $key) => $this->highlighted !== null ? $this->highlight(0) : null,
            Key::oneOf([Key::END, Key::CTRL_E], $key) => $this->highlighted !== null ? $this->highlight(count($this->matches()) - 1) : null,
            Key::ENTER => $this->selectHighlighted(),
            Key::oneOf([Key::LEFT, Key::LEFT_ARROW, Key::RIGHT, Key::RIGHT_ARROW, Key::CTRL_B, Key::CTRL_F], $key) => $this->highlighted = null,
            default => (function () {
                $this->highlighted = null;
                $this->matches = null;
                $this->firstVisible = 0;
            })(),
        });

        $this->trackTypedValue($default);
    }

    /**
     * Get the entered value with a virtual cursor.
     */
    public function valueWithCursor(int $maxWidth): string
    {
        if ($this->highlighted !== null) {
            return $this->value() === ''
                ? $this->dim($this->truncate($this->placeholder, $maxWidth))
                : $this->truncate($this->value(), $maxWidth);
        }

        if ($this->value() === '') {
            return $this->dim($this->addCursor($this->placeholder, 0, $maxWidth));
        }

        return $this->addCursor($this->value(), $this->cursorPosition, $maxWidth);
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

        if ($this->options instanceof Closure) {
            return $this->matches = array_values(($this->options)($this->value()));
        }

        return $this->matches = array_values(array_filter($this->options, function ($option) {
            return str_starts_with(strtolower($option), strtolower($this->value()));
        }));
    }

    /**
     * The current visible matches.
     *
     * @return array<string>
     */
    public function visible(): array
    {
        return array_slice($this->matches(), $this->firstVisible, $this->scroll, preserve_keys: true);
    }

    protected function highlightOffset(int $offset): void
    {
        if ($offset === 0 || $this->matches() === []) {
            return;
        }

        if (count($this->matches()) === 1) {
            $this->highlighted = $this->highlighted === null ? 0 : null;
        } elseif ($this->highlighted === 0 && $offset < 0) {
            $this->highlighted = null;
        } elseif ($this->highlighted === null) {
            $this->highlighted = $offset < 0 ? count($this->matches()) - 1 : 0;
        } elseif ($this->highlighted === count($this->matches()) - 1 && $offset > 0) {
            $this->highlighted = null;
        } else {
            $this->highlighted = $offset < 0 ? max(0, $this->highlighted + $offset) : min(count($this->matches()) - 1, $this->highlighted + $offset);
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
