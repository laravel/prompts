<?php

namespace Laravel\Prompts\Themes\Default;

use Laravel\Prompts\Concerns\Colors;
use Laravel\Prompts\TextPrompt;

class TextPromptRenderer
{
    use Colors;
    use Concerns\DrawsBoxes;

    /**
     * Render the text prompt.
     */
    public function __invoke(TextPrompt $prompt): string
    {
        return match ($prompt->state) {
            'error' => <<<EOT

                {$this->box($prompt->message, $prompt->valueWithCursor(), color: 'yellow')}
                {$this->yellow("  ⚠ {$prompt->error}")}

                EOT,

            'submit' => <<<EOT

                {$this->box($this->dim($prompt->message), $this->dim($prompt->value()))}

                EOT,

            'cancel' => <<<EOT

                {$this->box($prompt->message, $this->strikethrough($this->dim($prompt->value() ?: $prompt->placeholder)), color: 'red')}
                {$this->red('  ⚠ Cancelled.')}

                EOT,

            default => <<<EOT

                {$this->box($this->cyan($prompt->message), $prompt->valueWithCursor())}


                EOT,
        };
    }
}
