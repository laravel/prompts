<?php

namespace Laravel\Prompts\Themes\Default;

use Laravel\Prompts\SuggestPrompt;

class SuggestPromptRenderer extends Renderer
{
    use Concerns\DrawsBoxes;
    use Concerns\DrawsScrollbars;

    /**
     * Render the suggest prompt.
     */
    public function __invoke(SuggestPrompt $prompt): string
    {
        return match ($prompt->state) {
            'error' => $this
                ->box($prompt->label, $this->valueWithCursorAndArrow($prompt), $this->renderOptions($prompt), color: 'yellow')
                ->warning($prompt->error),

            'submit' => $this
                ->box($this->dim($prompt->label), $this->dim($prompt->value())),

            'cancel' => $this
                ->box($prompt->label, $this->strikethrough($this->dim($prompt->value() ?: $prompt->placeholder)), color: 'red')
                ->error('Cancelled'),

            default => $this
                ->box($this->cyan($prompt->label), $this->valueWithCursorAndArrow($prompt), $this->renderOptions($prompt))
                ->spaceForDropdown($prompt)
                ->newLine(), // Space for errors
        };
    }

    /**
     * Render the value with the cursor and an arrow.
     */
    protected function valueWithCursorAndArrow(SuggestPrompt $prompt): string
    {
        if ($prompt->highlighted !== null || $prompt->value() !== '' || count($prompt->matches()) === 0) {
            return $prompt->valueWithCursor();
        }

        return preg_replace(
            '/\s$/',
            $this->cyan('⌄'),
            $this->pad($prompt->valueWithCursor().'  ', $this->longest($prompt->matches(), padding: 2))
        );
    }

    /**
     * Render a spacer to prevent jumping when the suggestions are displayed.
     */
    protected function spaceForDropdown(SuggestPrompt $prompt): self
    {
        if ($prompt->value() === '' && $prompt->highlighted === null) {
            $this->newLine(min(count($prompt->matches()), $prompt->scroll) + 1);
        }

        return $this;
    }

    /**
     * Render the options.
     */
    protected function renderOptions(SuggestPrompt $prompt): string
    {
        if (empty($prompt->matches()) || ($prompt->value() === '' && $prompt->highlighted === null)) {
            return '';
        }

        return $this->scroll(
            collect($prompt->matches())
                ->map(fn ($label, $i) => $prompt->highlighted === $i
                    ? "{$this->cyan('›')} {$label}  "
                    : "  {$this->dim($label)}  "
                ),
            $prompt->highlighted,
            $prompt->scroll,
            $this->longest($prompt->matches(), padding: 4)
        )->implode(PHP_EOL);
    }
}
