<?php

namespace Laravel\Prompts;

use Closure;
use Illuminate\Support\Collection;

/**
 * @phpstan-type TOption = array{id: int|string, tab: string, body: string}
 */
class TabbedScrollableSelectPrompt extends Prompt
{
    /**
     * Index of the currently selected option.
     */
    public int|null $selected;

    public int $firstVisible = 0;

    public readonly int $width;

    /**
     * The processed content for the tabbed-scrollable-select prompt.
     * 
     * @var Collection<int, Collection<int, string>>
     */
    public readonly Collection $content;

    /**
     * The options for the tabbed-scrollable-select prompt.
     *
     * @var Collection<int, TOption>
     */
    public readonly Collection $options;

    /**
     * Create a new TabbedScrollableSelectPrompt instance.
     * 
     * @param  array<int, TOption>|Collection<int, TOption>  $options
     * @param int|Closure(Collection<int, TOption>): Collection<int, TOption> $default The default value for the prompt. If Closure, it is passed `$options` and should return a Collection containing only the desired record.
     */
    public function __construct(
        public string $label,
        array|Collection $options,
        int|Closure $default = 0,
        public int $scroll = 14,
        public int $max_width = 120,
        public bool|string $required = true,
        public mixed $validate = null,
        public string $hint = '',
    ) {
        $this->width = min($this->terminal()->cols(), $this->max_width);
        $this->options = $options instanceof Collection ? $options : collect($options);
        $this->selected = is_callable($default) ? $default($this->options)->keys()->sole() : $default;
        $this->scroll = max($this->scroll, 5); // Scrollbar is impractical below 5 lines.
        $this->scroll = min($this->scroll, 14); // Scrollbar breaks above 14 lines.

        $this->content = $this->options->map(function (array $option): Collection {
            return $this->processContentBody((string) $option['body'], $this->width);
        });

        $this->on('key', fn ($key) => match($key) {
            Key::ENTER => $this->submit(),
            Key::ESCAPE => $this->handleEscape(),
            Key::UP, Key::UP_ARROW => $this->handleUpArrow(),
            Key::DOWN, Key::DOWN_ARROW => $this->handleDownArrow(),
            Key::LEFT, Key::LEFT_ARROW => $this->handleLeftArrow(),
            Key::RIGHT, Key::RIGHT_ARROW => $this->handleRightArrow(),
            Key::oneOf([Key::HOME], $key) => $this->handleHome(),
            Key::oneOf([Key::END], $key) => $this->handleEnd(),
            Key::SHIFT_UP => $this->doTimes(5, fn () => $this->handleUpArrow()),
            Key::SHIFT_DOWN => $this->doTimes(5, fn () => $this->handleDownArrow()),
            default => null,
        });
    }

    /**
     * Get the selected value.
     */
    public function value(): int|string|null
    {
        return is_null($this->selected)
            ? null
            : $this->options->get($this->selected)['id'];
    }

    /**
     * Get the visible content for the prompt.
     *
     * @return Collection<int, string>
     */
    public function visible(): Collection
    {
        return $this->content->get($this->selected)->slice($this->firstVisible, $this->scroll);
    }

    /**
     * Get a collection of instructions for the prompt.
     *
     * @return Collection<int, string>
     */
    public function getInstructions(): Collection
    {
        $instructions = collect([
            'Use the [LEFT] and [RIGHT] arrow keys to navigate between options.',
            'Use the [UP] and [DOWN] arrow keys to scroll the selected area.',
            'Press [ENTER] to select the highlighted option.',
        ]);

        if (! $this->required) {
            $instructions->push('Press [ESCAPE] to select none of these options.');
        }

        return $instructions;
    }

    /**
     * Process the content body.
     *
     * @return Collection<int, string>
     */
    protected function processContentBody(string $body, int $width): Collection
    {
        return collect(explode(PHP_EOL, wordwrap($body, $width - 8, PHP_EOL)))->pad($this->scroll, '');
    }

    protected function doTimes(int $times, Closure $closure): void
    {
        for ($i = 0; $i < $times; $i++) $closure();
    }

    protected function handleUpArrow(): void
    {
        $this->firstVisible = max($this->firstVisible - 1, 0);
    }

    protected function handleDownArrow(): void
    {
        $this->firstVisible = min($this->firstVisible + 1, $this->content->get($this->selected)->count() - $this->scroll);
    }

    protected function handleHome(): void
    {
        $this->firstVisible = 0;
    }

    protected function handleEnd(): void
    {
        $this->firstVisible = $this->content->get($this->selected)->count() - $this->scroll;
    }

    protected function handleLeftArrow(): void
    {
        $this->firstVisible = 0;
        $this->selected = max($this->selected - 1, 0);
    }

    protected function handleRightArrow(): void
    {
        $this->firstVisible = 0;
        $this->selected = min($this->selected + 1, $this->options->count() - 1);
    }

    protected function handleEscape(): void
    {
        if ($this->required) {
            $this->error = 'You must select an option.';
            $this->state = 'error';

            return;
        }

        $this->selected = null;
        $this->state = 'submit';
    }
}
