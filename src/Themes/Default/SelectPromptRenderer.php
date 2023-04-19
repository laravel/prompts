<?php

namespace Laravel\Prompts\Themes\Default;

use Laravel\Prompts\SelectPrompt;

class SelectPromptRenderer extends Renderer
{
    use Concerns\DrawsBoxes;
    use Concerns\DrawsScrollbars;

    /**
     * Render the select prompt.
     */
    public function __invoke(SelectPrompt $prompt): string
    {
        return match ($prompt->state) {
            'submit' => $this
                ->box($this->dim($prompt->label), $this->dim($this->format($prompt->label()))),

            'cancel' => $this
                ->box($prompt->label, $this->renderOptions($prompt), color: 'red')
                ->error('Cancelled.'),

            'error' => $this
                ->box($prompt->label, $this->renderOptions($prompt), color: 'yellow')
                ->warning($prompt->error),

            default => $this
                ->box($this->cyan($prompt->label), $this->renderOptions($prompt))
                ->newLine(), // Space for errors
        };
    }

    /**
     * Render the options.
     */
    protected function renderOptions(SelectPrompt $prompt): string
    {
        return $this->scroll(
            collect($prompt->options)
                ->values()
                ->map(fn ($label, $i) => $prompt->highlighted === $i
                    ? "{$this->cyan('›')} {$this->cyan('●')} {$this->format($label)}  "
                    : "  {$this->dim('○')} {$this->dim($this->format($label))}  "
                ),
            $prompt->highlighted,
            $prompt->scroll,
            $this->longest($prompt->options, padding: 6)
        )->implode(PHP_EOL);
    }
}
