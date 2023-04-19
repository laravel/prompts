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
                ->box($this->dim($prompt->label), $this->dim($prompt->label())),

            'cancel' => $this
                ->box($prompt->label, $this->strikethrough($this->dim($prompt->label())), color: 'red')
                ->error('Cancelled.'),

            'error' => $this
                ->box($prompt->label, $this->renderOptions($prompt), color: 'yellow')
                ->warning($prompt->error),

            default => $this
                ->box($this->cyan($prompt->label), $this->renderOptions($prompt))
                ->newLine(), // Space for errors
        };
    }

    /**
     * Render the confirm prompt options.
     */
    protected function renderOptions(ConfirmPrompt $prompt): string
    {
        return $prompt->confirmed
            ? "{$this->green('●')} {$prompt->yes} {$this->dim('/ ○ '.$prompt->no)}"
            : "{$this->dim('○ '.$prompt->yes.' /')} {$this->green('●')} {$prompt->no}";
    }
}
