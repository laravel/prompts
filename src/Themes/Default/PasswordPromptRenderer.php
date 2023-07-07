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
        $maxWidth = $prompt->terminal()->cols() - 6;

        return match ($prompt->state) {
            'error' => $this
                ->box(
                    $this->dim($this->truncate($prompt->label, $prompt->terminal()->cols() - 6)),
                    $prompt->maskedWithCursor($maxWidth),
                    color: 'yellow',
                )
                ->warning($prompt->error),

            'submit' => $this
                ->box(
                    $this->dim($prompt->label),
                    $this->dim($this->truncate($prompt->masked(), $maxWidth)),
                ),

            'cancel' => $this
                ->box(
                    $this->truncate($prompt->label, $prompt->terminal()->cols() - 6),
                    $this->strikethrough($this->dim($this->truncate($prompt->masked(), $maxWidth))),
                    color: 'red',
                )
                ->error('Cancelled.'),

            default => $this
                ->box(
                    $this->cyan($this->truncate($prompt->label, $prompt->terminal()->cols() - 6)),
                    $prompt->maskedWithCursor($maxWidth),
                )
                ->newLine(), // Space for errors
        };
    }
}
