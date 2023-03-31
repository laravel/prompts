<?php

namespace Laravel\Prompts\Themes\Default;

use Laravel\Prompts\Concerns\Colors;
use Laravel\Prompts\PasswordPrompt;

class PasswordPromptRenderer
{
    use Colors;
    use Concerns\DrawsBoxes;

    /**
     * Render the password prompt.
     */
    public function __invoke(PasswordPrompt $prompt): string
    {
        return match ($prompt->state) {
            'error' => <<<EOT

                {$this->box($prompt->message, $prompt->maskedWithCursor(), color: 'yellow')}
                {$this->yellow("  ⚠ {$prompt->error}")}

                EOT,

            'submit' => <<<EOT

                {$this->box($this->dim($prompt->message), $this->dim($prompt->masked()))}

                EOT,

            'cancel' => <<<EOT

                {$this->box($prompt->message, $this->strikethrough($this->dim($prompt->masked())), color: 'red')}
                {$this->red('  ⚠ Cancelled.')}

                EOT,

            default => <<<EOT

                {$this->box($this->cyan($prompt->message), $prompt->maskedWithCursor())}


                EOT,
        };
    }
}
