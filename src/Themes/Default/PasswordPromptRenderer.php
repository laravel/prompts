<?php

namespace Laravel\Prompts\Themes\Default;

use Laravel\Prompts\PasswordPrompt;

class PasswordPromptRenderer extends Renderer
{
    use Concerns\DrawsBoxes;

    /**
     * Render the password prompt.
     */
    public function __invoke(PasswordPrompt $prompt): string
    {
        return match ($prompt->state) {
            'error' => $this
                ->box($prompt->label, $prompt->maskedWithCursor(), color: 'yellow')
                ->warning($prompt->error),

            'submit' => $this
                ->box($this->dim($prompt->label), $this->dim($prompt->masked())),

            'cancel' => $this
                ->box($prompt->label, $this->strikethrough($this->dim($prompt->masked())), color: 'red')
                ->error('Cancelled.'),

            default => $this
                ->box($this->cyan($prompt->label), $prompt->maskedWithCursor())
                ->newLine(), // Space for errors
        };
    }
}
