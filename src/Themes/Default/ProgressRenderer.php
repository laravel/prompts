<?php

namespace Laravel\Prompts\Themes\Default;

use Laravel\Prompts\Progress;

class ProgressRenderer extends Renderer
{
    use Concerns\DrawsBoxes;

    /**
     * The character to use for the progress bar.
     */
    protected string $barCharacter = '▆';

    /**
     * Render the progress bar.
     */
    public function __invoke(Progress $progressBar): string
    {
        $percentage = $progressBar->progress / $progressBar->total;

        $filled = $percentage === 0 ? '' : str_repeat($this->barCharacter, (int) ceil($percentage * $this->minWidth));

        return match ($progressBar->state) {
            'submit' => $this
                ->box(
                    $this->dim($this->truncate($progressBar->label, $progressBar->terminal()->cols() - 6)),
                    $this->dim($filled),
                ),

            'error' => $this
                ->box(
                    $this->truncate($progressBar->label, $progressBar->terminal()->cols() - 6),
                    $this->dim($filled),
                    color: 'red',
                ),

            default => $this
                ->box(
                    $this->cyan($this->truncate($progressBar->label, $progressBar->terminal()->cols() - 6)),
                    $this->dim($filled),
                )
                ->when(
                    $progressBar->itemLabel,
                    fn () => $this->hint($progressBar->itemLabel),
                    fn () => $this->newLine() // Space for errors
                )
        };
    }
}
