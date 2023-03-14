<?php

namespace Laravel\Prompts\Themes\Terkelg;

use Laravel\Prompts\Concerns\Colors;
use Laravel\Prompts\PasswordPrompt;

class PasswordPromptRenderer
{
    use Colors;

    public function __invoke(PasswordPrompt $prompt)
    {
        return match ($prompt->state) {
            'error' => <<<EOT
                {$this->cyan('?')} {$this->bold($prompt->message)} {$this->dim('›')} {$this->red($this->underline($prompt->maskedWithCursor()))}
                {$this->dim('›')} {$this->red($this->italic($prompt->error))}

                EOT,

            'submit' => <<<EOT
                {$this->green('✔')} {$this->bold($prompt->message)} {$this->dim('…')} {$prompt->masked()}

                EOT,

            'cancel' => <<<EOT
                {$this->red('✖')} {$this->bold($prompt->message)} {$this->dim('…')} {$prompt->masked()}

                EOT,

            default => <<<EOT
                {$this->cyan('?')} {$this->bold($prompt->message)} {$this->dim('›')} {$prompt->maskedWithCursor()}


                EOT,
        };
    }
}
