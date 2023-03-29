<?php

namespace Laravel\Prompts\Themes\Default;

use Laravel\Prompts\Concerns\Colors;
use Laravel\Prompts\SelectPrompt;

class SelectPromptRenderer
{
    use Colors;
    use Concerns\DrawsBoxes;

    public function __invoke(SelectPrompt $prompt)
    {
        return match ($prompt->state) {
            'submit' => <<<EOT

                {$this->box($this->dim($prompt->message), $this->dim($prompt->label()))}

                EOT,

            'cancel' => <<<EOT

                {$this->box($prompt->message, $this->renderOptions($prompt), 'red')}
                {$this->red('  ⚠ Cancelled.')}

                EOT,

            default => <<<EOT

                {$this->box($this->cyan($prompt->message), $this->renderOptions($prompt))}


                EOT,
        };
    }

    protected function renderOptions($prompt)
    {
        return collect($prompt->options)
            ->values()
            ->map(fn ($label, $i) => $prompt->highlighted === $i
                ? "› {$this->green('●')} {$label}"
                : "  {$this->dim('○')} {$this->dim($label)}"
            )
            ->implode(PHP_EOL);
    }
}
