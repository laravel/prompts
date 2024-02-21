<?php

namespace Laravel\Prompts;

use Closure;
use Illuminate\Support\Collection;

class MultiSelectPrompt extends Prompt
{
    use Concerns\Scrolling;

    /**
     * The options for the multi-select prompt.
     *
     * @var array<int|string, string>|Closure
     */
    public array|Closure $options;

    /**
     * The default values the multi-select prompt.
     *
     * @var array<int|string>
     */
    public array $default;

    /**
     * The selected values.
     *
     * @var array<int|string>
     */
    protected array $values = [];

    /**
     * Create a new MultiSelectPrompt instance.
     *
     * @param  array<int|string, string>|Collection<int|string, string>|Closure  $options
     * @param  array<int|string>|Collection<int, int|string>  $default
     */
    public function __construct(
        public string $label,
        array|Collection|Closure $options,
        array|Collection $default = [],
        public int $scroll = 5,
        public bool|string $required = false,
        public mixed $validate = null,
        public string $hint = '',
    ) {
        $this->options = $options instanceof Collection ? $options->all() : $options;
        $this->default = $default instanceof Collection ? $default->all() : $default;
        $this->values = $this->default;

        $this->initializeScrolling(0);

        $options = $this->eval($this->options);

        $this->on('key', fn ($key) => match ($key) {
            Key::UP, Key::UP_ARROW, Key::LEFT, Key::LEFT_ARROW, Key::SHIFT_TAB, Key::CTRL_P, Key::CTRL_B, 'k', 'h' => $this->highlightPrevious(count($options)),
            Key::DOWN, Key::DOWN_ARROW, Key::RIGHT, Key::RIGHT_ARROW, Key::TAB, Key::CTRL_N, Key::CTRL_F, 'j', 'l' => $this->highlightNext(count($options)),
            Key::oneOf([Key::HOME, Key::CTRL_A], $key) => $this->highlight(0),
            Key::oneOf([Key::END, Key::CTRL_E], $key) => $this->highlight(count($options) - 1),
            Key::SPACE => $this->toggleHighlighted(),
            Key::ENTER => $this->submit(),
            default => null,
        });
    }

    /**
     * Get the selected values.
     *
     * @return array<int|string>
     */
    public function value(): array
    {
        return array_values($this->values);
    }

    /**
     * Get the selected labels.
     *
     * @return array<string>
     */
    public function labels(): array
    {
        $options = $this->eval($this->options);

        if (array_is_list($options)) {
            return array_map(fn ($value) => (string) $value, $this->values);
        }

        return array_values(array_intersect_key($options, array_flip($this->values)));
    }

    /**
     * The currently visible options.
     *
     * @return array<int|string, string>
     */
    public function visible(): array
    {
        return array_slice(
            $this->eval($this->options),
            $this->firstVisible,
            $this->scroll,
            preserve_keys: true,
        );
    }

    /**
     * Check whether the value is currently highlighted.
     */
    public function isHighlighted(string $value): bool
    {
        $options = $this->eval($this->options);

        if (array_is_list($options)) {
            return $options[$this->highlighted] === $value;
        }

        return array_keys($options)[$this->highlighted] === $value;
    }

    /**
     * Check whether the value is currently selected.
     */
    public function isSelected(string $value): bool
    {
        return in_array($value, $this->values);
    }

    /**
     * Toggle the highlighted entry.
     */
    protected function toggleHighlighted(): void
    {
        $options = $this->eval($this->options);

        $value = array_is_list($options)
            ? $options[$this->highlighted]
            : array_keys($options)[$this->highlighted];

        if (in_array($value, $this->values)) {
            $this->values = array_filter($this->values, fn ($v) => $v !== $value);
        } else {
            $this->values[] = $value;
        }

        $this->validate($this->value());
    }

    /**
     * Returns options as an array; and re-evaluates them if they were in a closure
     * 
     * @param  array<int|string, string>|Collection<int|string, string>|Closure  $options
     * @return array
     */
    public function eval(array|Collection|Closure $options): array
    {
        return ($options = value($options)) instanceof Collection ? $options->toArray() : (array) $options;
    }
}
