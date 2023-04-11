<?php

namespace Laravel\Prompts\Themes\Default;

use Laravel\Prompts\Concerns\Colors;
use Laravel\Prompts\ConfirmPrompt;

class ConfirmPromptRenderer
{
    use Colors;
    use Concerns\DrawsBoxes;

    /**
     * Render the confirm prompt.
     */
    public function __invoke(ConfirmPrompt $prompt): string
    {
        return match ($prompt->state) {
            'submit' => <<<EOT

                {$this->box($this->dim($prompt->label), $this->dim($prompt->label()))}

                EOT,

            'cancel' => <<<EOT

                {$this->box($prompt->label, $this->strikethrough($this->dim($prompt->label())), color: 'red')}
                {$this->red('  ⚠ Cancelled.')}

                EOT,

            default => <<<EOT

                {$this->box($this->cyan($prompt->label), $this->renderOptions($prompt))}


                EOT,
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
