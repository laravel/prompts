<?php

namespace Laravel\Prompts\Themes\Default;

use Laravel\Prompts\MultiSearchPrompt;

class MultiSearchPromptRenderer extends Renderer
{
    use Concerns\DrawsBoxes;
    use Concerns\DrawsScrollbars;

    /**
     * Render the suggest prompt.
     */
    public function __invoke(MultiSearchPrompt $prompt): string
    {
        $prompt->scroll = min($prompt->scroll, $prompt->terminal()->lines() - 7);
        $maxWidth = $prompt->terminal()->cols() - 6;

        return match ($prompt->state) {
            'submit' => $this
                ->box(
                    $this->dim($this->truncate($prompt->label, $prompt->terminal()->cols() - 6)),
                    $this->renderSelectedOptions($prompt)
                ),

            'cancel' => $this
                ->box(
                    $this->dim($this->truncate($prompt->label, $prompt->terminal()->cols() - 6)),
                    $this->strikethrough($this->dim($this->truncate($prompt->searchValue() ?: $prompt->placeholder, $maxWidth))),
                    color: 'red',
                )
                ->error('Cancelled'),

            'error' => $this
                ->box(
                    $this->truncate($prompt->label, $prompt->terminal()->cols() - 6),
                    $prompt->valueWithCursor($maxWidth),
                    $this->renderOptions($prompt),
                    color: 'yellow',
                )
                ->warning($this->truncate($prompt->error, $prompt->terminal()->cols() - 5)),

            'searching' => $this
                ->box(
                    $this->cyan($this->truncate($prompt->label, $prompt->terminal()->cols() - 6)),
                    $this->valueWithCursorAndSearchIcon($prompt, $maxWidth),
                    $this->renderOptions($prompt),
                )
                ->hint($prompt->hint),

            default => $this
                ->box(
                    $this->cyan($this->truncate($prompt->label, $prompt->terminal()->cols() - 6)),
                    $prompt->valueWithCursor($maxWidth),
                    $this->renderOptions($prompt),
                )
                ->when(
                    $prompt->hint,
                    fn () => $this->hint($prompt->hint),
                    fn () => $this->newLine() // Space for errors
                )
                ->spaceForDropdown($prompt)
                ->newLine(), // Space for errors
        };
    }

    /**
     * Render the value with the cursor and a search icon.
     */
    protected function valueWithCursorAndSearchIcon(MultiSearchPrompt $prompt, int $maxWidth): string
    {
        return preg_replace(
            '/\s$/',
            $this->cyan('…'),
            $this->pad($prompt->valueWithCursor($maxWidth - 1).'  ', min($this->longest($prompt->matches(), padding: 2), $maxWidth))
        );
    }

    /**
     * Render a spacer to prevent jumping when the suggestions are displayed.
     */
    protected function spaceForDropdown(MultiSearchPrompt $prompt): self
    {
        if ($prompt->searchValue() !== '') {
            return $this;
        }

        $this->newLine(max(
            0,
            min($prompt->scroll, $prompt->terminal()->lines() - 7) - count($prompt->matches()),
        ));

        if ($prompt->matches() === []) {
            $this->newLine();
        }

        return $this;
    }

    /**
     * Render the options.
     */
    protected function renderOptions(MultiSearchPrompt $prompt): string
    {
        if ($prompt->searchValue() !== '' && empty($prompt->matches())) {
            return $this->gray('  '.($prompt->state === 'searching' ? 'Searching...' : 'No results.'));
        }

        return $this->scrollbar(
            collect($prompt->visible())
                ->map(fn ($label) => $this->truncate($label, $prompt->terminal()->cols() - 10))
                ->map(function ($label, $key) use ($prompt) {
                    $index = array_search($key, array_keys($prompt->matches()));
                    $active = $index === $prompt->highlighted;
                    $matches = $prompt->matches();

                    if ($prompt->returnKeys) {
                        $value = array_keys($matches)[$index];
                    } else {
                        $value = $matches[$index];
                    }
                    $selected = in_array($value, $prompt->value());

                    return match (true) {
                        $active && $selected => "{$this->cyan('› ◼')} {$label}  ",
                        $active => "{$this->cyan('›')} ◻ {$label}  ",
                        $selected => "  {$this->cyan('◼')} {$this->dim($label)}  ",
                        default => "  {$this->dim('◻')} {$this->dim($label)}  ",
                    };
                }),
            $prompt->firstVisible,
            $prompt->scroll,
            count($prompt->matches()),
            min($this->longest($prompt->matches(), padding: 4), $prompt->terminal()->cols() - 6)
        )->implode(PHP_EOL);
    }

    /**
     * Render the selected options.
     */
    protected function renderSelectedOptions(MultiSearchPrompt $prompt): string
    {
        if (count($prompt->labels()) === 0) {
            return $this->gray('None');
        }

        return implode("\n", array_map(
            fn ($label) => $this->truncate($label, $prompt->terminal()->cols() - 6),
            $prompt->labels()
        ));
    }
}
