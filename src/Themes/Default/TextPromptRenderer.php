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
        $maxWidth = $prompt->terminal()->cols() - 6;

        return match ($prompt->state) {
            'error' => $this
                ->box(
                    $this->truncate($prompt->label, $prompt->terminal()->cols() - 6),
                    $prompt->valueWithCursor($maxWidth),
                    color: 'yellow',
                )
                ->warning($prompt->error),

            'submit' => $this
                ->box(
                    $this->dim($this->truncate($prompt->label, $prompt->terminal()->cols() - 6)),
                    $this->dim($this->truncate($prompt->value(), $maxWidth)),
                ),

            'cancel' => $this
                ->box(
                    $this->truncate($prompt->label, $prompt->terminal()->cols() - 6),
                    $this->strikethrough($this->dim($this->truncate($prompt->value() ?: $prompt->placeholder, $maxWidth))),
                    color: 'red',
                )
                ->error('Cancelled.'),

            default => $this
                ->box(
                    $this->cyan($this->truncate($prompt->label, $prompt->terminal()->cols() - 6)),
                    $prompt->valueWithCursor($maxWidth),
                )
                ->newLine(), // Space for errors
        };
    }
}
