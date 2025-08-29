<?php

namespace Laravel\Prompts\Themes\Default;

use Laravel\Prompts\Prompt;
use Laravel\Prompts\TextareaPrompt;
use Laravel\Prompts\Themes\Contracts\Scrolling;

class TextareaPromptRenderer extends Renderer implements Scrolling
{
    use Concerns\DrawsBoxes;
    use Concerns\DrawsScrollbars;
    use Concerns\RendersDescription;

    /**
     * Render the textarea prompt.
     */
    public function __invoke(TextareaPrompt $prompt): string
    {
        $prompt->width = $prompt->terminal()->cols() - 8;
        $maxWidth = $prompt->terminal()->cols() - 6;
        $hasDescription = $prompt->description && trim($prompt->description) !== '';

        return match ($prompt->state) {
            'submit' => $this
                ->box(
                    $this->dim($this->truncate($prompt->label, $prompt->width)),
                    $hasDescription ? $this->renderDescription($prompt, $maxWidth, fn () => $this->calculateDescriptionWidth($prompt, $maxWidth)) : implode(PHP_EOL, $prompt->lines()),
                    $hasDescription ? implode(PHP_EOL, $prompt->lines()) : '',
                ),

            'cancel' => $this
                ->box(
                    $this->truncate($prompt->label, $prompt->width),
                    $hasDescription ? $this->renderDescription($prompt, $maxWidth, fn () => $this->calculateDescriptionWidth($prompt, $maxWidth)) : implode(PHP_EOL, array_map(fn ($line) => $this->strikethrough($this->dim($line)), $prompt->lines())),
                    $hasDescription ? implode(PHP_EOL, array_map(fn ($line) => $this->strikethrough($this->dim($line)), $prompt->lines())) : '',
                    color: 'red',
                )
                ->error($prompt->cancelMessage),

            'error' => $this
                ->box(
                    $this->truncate($prompt->label, $prompt->width),
                    $hasDescription ? $this->renderDescription($prompt, $maxWidth, fn () => $this->calculateDescriptionWidth($prompt, $maxWidth)) : $this->renderText($prompt),
                    $hasDescription ? $this->renderText($prompt) : '',
                    color: 'yellow',
                    info: 'Ctrl+D to submit'
                )
                ->warning($this->truncate($prompt->error, $prompt->terminal()->cols() - 5)),

            default => $this
                ->box(
                    $this->cyan($this->truncate($prompt->label, $prompt->width)),
                    $hasDescription ? $this->renderDescription($prompt, $maxWidth, fn () => $this->calculateDescriptionWidth($prompt, $maxWidth)) : $this->renderText($prompt),
                    $hasDescription ? $this->renderText($prompt) : '',
                    info: 'Ctrl+D to submit'
                )
                ->when(
                    $prompt->hint,
                    fn () => $this->hint($prompt->hint),
                    fn () => $this->newLine() // Space for errors
                )
        };
    }

    /**
     * Calculate the description width based on textarea content.
     */
    protected function calculateDescriptionWidth(Prompt $prompt, int $maxWidth): int
    {
        if (! $prompt instanceof TextareaPrompt) {
            return $this->minWidth;
        }

        $titleWidth = mb_strwidth($this->stripEscapeSequences($prompt->label));
        $textareaWidth = min($prompt->width, $maxWidth);

        return max($this->minWidth, max($titleWidth, $textareaWidth));
    }

    /**
     * Render the text in the prompt.
     */
    protected function renderText(TextareaPrompt $prompt): string
    {
        $visible = $prompt->visible();

        while (count($visible) < $prompt->scroll) {
            $visible[] = '';
        }

        $longest = $this->longest($prompt->lines()) + 2;

        return implode(PHP_EOL, $this->scrollbar(
            $visible,
            $prompt->firstVisible,
            $prompt->scroll,
            count($prompt->lines()),
            min($longest, $prompt->width + 2),
        ));
    }

    /**
     * The number of lines to reserve outside of the scrollable area.
     */
    public function reservedLines(): int
    {
        return 5;
    }
}
