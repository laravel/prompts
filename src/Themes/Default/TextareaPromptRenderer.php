<?php

namespace Laravel\Prompts\Themes\Default;

use Laravel\Prompts\TextareaPrompt;
use Laravel\Prompts\Themes\Contracts\Scrolling;

class TextareaPromptRenderer extends Renderer implements Scrolling
{
    use Concerns\DrawsBoxes;
    use Concerns\DrawsScrollbars;

    /**
     * Render the textarea prompt.
     */
    public function __invoke(TextareaPrompt $prompt): string
    {
        $maxWidth = $prompt->terminal()->cols() - 6;

        return match ($prompt->state) {
            'submit' => $this
                ->box(
                    $this->dim($this->truncate($prompt->label, $prompt->terminal()->cols() - 6)),
                    collect($prompt->lines())->implode(PHP_EOL),
                ),

            'cancel' => $this
                ->box(
                    $this->truncate($prompt->label, $prompt->terminal()->cols() - 6),
                    $this->strikethrough($this->dim(collect($prompt->lines())->implode(PHP_EOL))),
                    color: 'red',
                )
                ->error('Cancelled.'),

            'error' => $this
                ->box(
                    $this->truncate($prompt->label, $prompt->terminal()->cols() - 6),
                    $this->renderText($prompt),
                    color: 'yellow',
                    info: 'Ctrl+D to submit'
                )
                ->warning($this->truncate($prompt->error, $prompt->terminal()->cols() - 5)),

            default => $this
                ->box(
                    $this->cyan($this->truncate($prompt->label, $prompt->terminal()->cols() - 6)),
                    $this->renderText($prompt),
                    info: 'Ctrl+D to submit'
                )
                ->when(
                    $prompt->hint,
                    fn () => $this->hint($prompt->hint),
                    fn () => $this->newLine() // Space for errors
                )
        };
    }

    protected function renderText(TextareaPrompt $prompt): string
    {
        return $this->scrollbar(
            collect($prompt->visible()),
            $prompt->firstVisible,
            $prompt->scroll,
            count($prompt->lines()),
            $this->minWidth,
        )->implode(PHP_EOL);
    }

    /**
     * The number of lines to reserve outside of the scrollable area.
     */
    public function reservedLines(): int
    {
        return 5;
    }
}
