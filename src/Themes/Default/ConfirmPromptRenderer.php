<?php

namespace Laravel\Prompts\Themes\Default;

use Laravel\Prompts\ConfirmPrompt;

class ConfirmPromptRenderer extends Renderer
{
    use Concerns\DrawsBoxes;

    /**
     * Render the confirm prompt.
     */
    public function __invoke(ConfirmPrompt $prompt): string
    {
        return match ($prompt->state) {
            'submit' => $this
                ->box(
                    $this->dim($this->truncate($prompt->label, $prompt->terminal()->cols() - 6)),
                    $this->dim($this->truncate($prompt->label(), $prompt->terminal()->cols() - 6))
                ),

            'cancel' => $this
                ->box(
                    $this->truncate($prompt->label, $prompt->terminal()->cols() - 6),
                    $this->strikethrough($this->dim($this->renderOptions($prompt))),
                    color: 'red'
                )
                ->error('Cancelled.'),

            'error' => $this
                ->box(
                    $this->truncate($prompt->label, $prompt->terminal()->cols() - 6),
                    $this->renderOptions($prompt),
                    color: 'yellow',
                )
                ->warning($prompt->error),

            default => $this
                ->box(
                    $this->cyan($this->truncate($prompt->label, $prompt->terminal()->cols() - 6)),
                    $this->renderOptions($prompt),
                )
                ->newLine(), // Space for errors
        };
    }

    /**
     * Render the confirm prompt options.
     */
    protected function renderOptions(ConfirmPrompt $prompt): string
    {
        $length = (int) floor(($prompt->terminal()->cols() - 14) / 2);
        $yes = $this->truncate($prompt->yes, $length);
        $no = $this->truncate($prompt->no, $length);

        return $prompt->confirmed
            ? "{$this->green('●')} {$yes} {$this->dim('/ ○ '.$no)}"
            : "{$this->dim('○ '.$yes.' /')} {$this->green('●')} {$no}";
    }
}
