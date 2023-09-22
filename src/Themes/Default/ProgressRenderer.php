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
    public function __invoke(Progress $progress): string
    {
        $filled = str_repeat($this->barCharacter, (int) ceil($progress->percentage() * $this->minWidth));

        return match ($progress->state) {
            'submit' => $this
                ->box(
                    $this->dim($this->truncate($progress->label, $progress->terminal()->cols() - 6)),
                    $this->dim($filled),
                ),

            'error' => $this
                ->box(
                    $this->truncate($progress->label, $progress->terminal()->cols() - 6),
                    $this->dim($filled),
                    color: 'red',
                ),

            default => $this
                ->box(
                    $this->cyan($this->truncate($progress->label, $progress->terminal()->cols() - 6)),
                    $this->dim($filled),
                )
                ->when(
                    $progress->itemLabel,
                    fn () => $this->hint($progress->itemLabel),
                    fn () => $this->newLine() // Space for errors
                )
        };
    }
}
