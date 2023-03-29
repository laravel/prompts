<?php

namespace Laravel\Prompts\Themes\Default;

use Laravel\Prompts\Concerns\Colors;
use Laravel\Prompts\PasswordPrompt;

class PasswordPromptRenderer
{
    use Colors;
    use Concerns\DrawsBoxes;

    public function __invoke(PasswordPrompt $prompt)
    {
        return match ($prompt->state) {
            'error' => <<<EOT

                {$this->box($prompt->message, $prompt->maskedWithCursor(), 'yellow')}
                {$this->yellow("  ⚠ {$prompt->error}")}

                EOT,

            'submit' => <<<EOT

                {$this->box($this->dim($prompt->message), $this->dim($prompt->masked()))}

                EOT,

            'cancel' => <<<EOT

                {$this->box($prompt->message, $this->strikethrough($this->dim($prompt->masked())), 'red')}
                {$this->red('  ⚠ Cancelled.')}

                EOT,

            default => <<<EOT

                {$this->box($this->cyan($prompt->message), $prompt->maskedWithCursor())}


                EOT,
        };
    }
}
