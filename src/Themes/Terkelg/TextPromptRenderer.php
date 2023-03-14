<?php

namespace Laravel\Prompts\Themes\Terkelg;

use Laravel\Prompts\Concerns\Colors;
use Laravel\Prompts\TextPrompt;

class TextPromptRenderer
{
    use Colors;

    public function __invoke(TextPrompt $prompt)
    {
        return match ($prompt->state) {
            'error' => <<<EOT
                {$this->cyan('?')} {$this->bold($prompt->message)} {$this->dim('›')} {$this->red($this->underline($prompt->valueWithCursor()))}
                {$this->dim('›')} {$this->red($this->italic($prompt->error))}

                EOT,

            'submit' => <<<EOT
                {$this->green('✔')} {$this->bold($prompt->message)} {$this->dim('…')} {$prompt->value()}

                EOT,

            'cancel' => <<<EOT
                {$this->red('✖')} {$this->bold($prompt->message)} {$this->dim('…')} {$prompt->value()}

                EOT,

            default => <<<EOT
                {$this->cyan('?')} {$this->bold($prompt->message)} {$this->dim('›')} {$prompt->valueWithCursor()}


                EOT,
        };
    }
}
