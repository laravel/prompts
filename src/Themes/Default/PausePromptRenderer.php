<?php

namespace Laravel\Prompts\Themes\Default;

use Laravel\Prompts\PausePrompt;

class PausePromptRenderer extends Renderer
{
    use Concerns\DrawsBoxes;

    /**
     * Render the pause prompt.
     */
    public function __invoke(PausePrompt $prompt): string
    {
        return match ($prompt->state) {
            'submit' => $this
                ->box(
                    $this->dim($this->truncate($prompt->title, $prompt->terminal()->cols() - 6)),
                    $this->renderBody($prompt),
                ),

            'cancel' => $this
                ->box(
                    $this->truncate($prompt->title, $prompt->terminal()->cols() - 6),
                    $this->renderBody($prompt),
                    color: 'red',
                )
                ->error('Cancelled.'),

            'error' => $this
                ->box(
                    $this->truncate($prompt->title, $prompt->terminal()->cols() - 6),
                    $this->renderBody($prompt),
                    color: 'yellow',
                    info: $this->truncate($prompt->info, $prompt->terminal()->cols() - 15),
                )
                ->warning($this->truncate($prompt->error, $prompt->terminal()->cols() - 5)),

            default => $this
                ->box(
                    $this->cyan($this->truncate($prompt->title, $prompt->terminal()->cols() - 6)),
                    $this->renderBody($prompt),
                    info: $this->magenta($this->truncate($prompt->info, $prompt->terminal()->cols() - 15)),
                )
                ->when(
                    $prompt->hint,
                    fn () => $this->hint($prompt->hint),
                    fn () => $this->newLine() // Space for errors
                ),
        };
    }

    /**
     * Render the pause body.
     */
    protected function renderBody(PausePrompt $prompt): string
    {
        $maxRowCols = $prompt->terminal()->cols() - 6;
        $rows = [];
        $currentRow = '';
        $wordsArray = explode(' ', $prompt->body);
        while (count($wordsArray) > 0) {
            $word = ' ' . $wordsArray[array_key_first($wordsArray)];
            if (mb_strlen($currentRow) + mb_strlen($word) <= $maxRowCols) {
                $currentRow .= $word;
                unset($wordsArray[array_key_first($wordsArray)]);
            } else {
                $rows[] = $currentRow;
                $currentRow = '';
            }
        }
        return implode("\n", $rows);
    }
}
