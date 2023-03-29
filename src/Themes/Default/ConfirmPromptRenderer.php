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

                {$this->box($this->dim($prompt->message), $this->dim($prompt->confirmed ? 'Yes' : 'No'))}

                EOT,

            'cancel' => <<<EOT

                {$this->box($prompt->message, $this->strikethrough($this->dim($prompt->confirmed ? 'Yes' : 'No')), 'red')}
                {$this->red('  ⚠ Cancelled.')}

                EOT,

            default => <<<EOT

                {$this->box($this->cyan($prompt->message), $this->renderOptions($prompt))}


                EOT,
        };
    }

    /**
     * Render the confirm prompt options.
     */
    protected function renderOptions(ConfirmPrompt $prompt): string
    {
        return $prompt->confirmed
            ? "{$this->green('●')} Yes {$this->dim('/ ○ No')}"
            : "{$this->dim('○ Yes /')} {$this->green('●')} No";
    }
}
