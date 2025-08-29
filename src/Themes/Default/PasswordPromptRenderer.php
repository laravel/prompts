<?php

namespace Laravel\Prompts\Themes\Default;

use Laravel\Prompts\PasswordPrompt;
use Laravel\Prompts\Prompt;

class PasswordPromptRenderer extends Renderer
{
    use Concerns\DrawsBoxes;
    use Concerns\RendersDescription;

    /**
     * Render the password prompt.
     */
    public function __invoke(PasswordPrompt $prompt): string
    {
        $maxWidth = $prompt->terminal()->cols() - 6;
        $hasDescription = $prompt->description && trim($prompt->description) !== '';

        return match ($prompt->state) {
            'submit' => $this
                ->box(
                    $this->dim($prompt->label),
                    $hasDescription ? $this->renderDescription($prompt, $maxWidth, fn () => $this->calculateDescriptionWidth($prompt, $maxWidth)) : $this->truncate($prompt->masked(), $maxWidth),
                    $hasDescription ? $this->truncate($prompt->masked(), $maxWidth) : '',
                ),

            'cancel' => $this
                ->box(
                    $this->truncate($prompt->label, $prompt->terminal()->cols() - 6),
                    $hasDescription ? $this->renderDescription($prompt, $maxWidth, fn () => $this->calculateDescriptionWidth($prompt, $maxWidth)) : $this->strikethrough($this->dim($this->truncate($prompt->masked() ?: $prompt->placeholder, $maxWidth))),
                    $hasDescription ? $this->strikethrough($this->dim($this->truncate($prompt->masked() ?: $prompt->placeholder, $maxWidth))) : '',
                    color: 'red',
                )
                ->error($prompt->cancelMessage),

            'error' => $this
                ->box(
                    $this->dim($this->truncate($prompt->label, $prompt->terminal()->cols() - 6)),
                    $hasDescription ? $this->renderDescription($prompt, $maxWidth, fn () => $this->calculateDescriptionWidth($prompt, $maxWidth)) : $prompt->maskedWithCursor($maxWidth),
                    $hasDescription ? $prompt->maskedWithCursor($maxWidth) : '',
                    color: 'yellow',
                )
                ->warning($this->truncate($prompt->error, $prompt->terminal()->cols() - 5)),

            default => $this
                ->box(
                    $this->cyan($this->truncate($prompt->label, $prompt->terminal()->cols() - 6)),
                    $hasDescription ? $this->renderDescription($prompt, $maxWidth, fn () => $this->calculateDescriptionWidth($prompt, $maxWidth)) : $prompt->maskedWithCursor($maxWidth),
                    $hasDescription ? $prompt->maskedWithCursor($maxWidth) : '',
                )
                ->when(
                    $prompt->hint,
                    fn () => $this->hint($prompt->hint),
                    fn () => $this->newLine() // Space for errors
                ),
        };
    }

    /**
     * Calculate the description width based on password input content.
     */
    protected function calculateDescriptionWidth(Prompt $prompt, int $maxWidth): int
    {
        if (! $prompt instanceof PasswordPrompt) {
            return $this->minWidth;
        }

        $titleWidth = mb_strwidth($this->stripEscapeSequences($prompt->label));
        $inputWidth = max(
            mb_strwidth($this->stripEscapeSequences($prompt->masked() ?: $prompt->placeholder)),
            40 // minimum reasonable input width
        );

        return max($this->minWidth, max($titleWidth, min($inputWidth, $maxWidth)));
    }
}
