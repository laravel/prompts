<?php

namespace Laravel\Prompts\Themes\Terkelg;

use Laravel\Prompts\Concerns\Colors;
use Laravel\Prompts\ConfirmPrompt;

class ConfirmPromptRenderer
{
    use Colors;

    public function __invoke(ConfirmPrompt $prompt)
    {
        return match ($prompt->state) {
            'submit' => <<<EOT
                {$this->green('✔')} {$this->bold($prompt->message)} {$this->dim('…')} {$this->selected($prompt)}

                EOT,

            'cancel' => <<<EOT
                {$this->red('✖')} {$this->bold($prompt->message)} {$this->dim('…')} {$this->selected($prompt)}

                EOT,

            default => <<<EOT
                {$this->cyan('?')} {$this->bold($prompt->message)} {$this->dim('›')} {$this->renderOptions($prompt)}

                EOT,
        };
    }

    protected function selected($prompt)
    {
        return $prompt->confirmed ? 'yes' : 'no';
    }

    protected function renderOptions($prompt)
    {
        return $prompt->confirmed
            ? "{$this->underline($this->cyan('yes'))} {$this->dim('/')} no"
            : "yes {$this->dim('/')} {$this->underline($this->cyan('no'))}";
    }
}
