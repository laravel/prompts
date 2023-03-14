<?php

namespace Laravel\Prompts\Themes\Terkelg;

use Laravel\Prompts\Concerns\Colors;
use Laravel\Prompts\SelectPrompt;

class SelectPromptRenderer
{
    use Colors;

    public function __invoke(SelectPrompt $prompt)
    {
        return match ($prompt->state) {
            'submit' => <<<EOT
                {$this->green('✔')} {$this->bold($prompt->message)} {$this->dim('…')} {$prompt->label()}

                EOT,

            'cancel' => <<<EOT
                {$this->red('✖')} {$this->bold($prompt->message)} {$this->dim('…')} {$prompt->label()}

                EOT,

            default => <<<EOT
                {$this->cyan('?')} {$this->bold($prompt->message)} {$this->dim('›')}
                {$this->renderOptions($prompt)}

                EOT,
        };
    }

    protected function renderOptions($prompt)
    {
        return collect($prompt->options)
            ->values()
            ->map(fn ($label, $i) => $prompt->highlighted === $i
                ? "{$this->bold($this->cyan('❯'))}   {$this->underline($this->cyan($label))}"
                : "    $label"
            )
            ->implode(PHP_EOL);
    }
}
