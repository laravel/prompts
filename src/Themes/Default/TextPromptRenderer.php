<?php

namespace Laravel\Prompts\Themes\Default;

use Laravel\Prompts\TextPrompt;

class TextPromptRenderer extends Renderer
{
    use Concerns\DrawsBoxes;

    /**
     * Render the text prompt.
     */
    public function __invoke(TextPrompt $prompt): string
    {
        return match ($prompt->state) {
            'error' => $this
                ->box($prompt->label, $prompt->valueWithCursor(), color: 'yellow')
                ->warning($prompt->error),

            'submit' => $this
                ->box($this->dim($prompt->label), $this->dim($prompt->value())),

            'cancel' => $this
                ->box($prompt->label, $this->strikethrough($this->dim($prompt->value() ?: $prompt->placeholder)), color: 'red')
                ->error('Cancelled.'),

            default => $this
                ->box($this->cyan($prompt->label), $prompt->valueWithCursor())
                ->newLine(), // Space for errors
        };
    }
}
