<?php

namespace Laravel\Prompts\Themes\Default;

use Laravel\Prompts\Concerns\Colors;
use Laravel\Prompts\SelectPrompt;

class SelectPromptRenderer
{
    use Colors;
    use Concerns\DrawsBoxes;

    /**
     * Render the select prompt.
     */
    public function __invoke(SelectPrompt $prompt): string
    {
        return match ($prompt->state) {
            'submit' => <<<EOT

                {$this->box($this->dim($prompt->message), $this->dim($prompt->label()))}

                EOT,

            'cancel' => <<<EOT

                {$this->box($prompt->message, $this->renderOptions($prompt), color: 'red')}
                {$this->red('  ⚠ Cancelled.')}

                EOT,

            default => <<<EOT

                {$this->box($this->cyan($prompt->message), $this->renderOptions($prompt))}


                EOT,
        };
    }

    /**
     * Render the options.
     */
    protected function renderOptions(SelectPrompt $prompt): string
    {
        $width = $this->longest($prompt->options, padding: 6);

        $lines = collect($prompt->scrolledLabels());

        return $lines
            ->map(fn ($label, $i) => $prompt->highlighted === $i
                ? "{$this->cyan('›')} {$this->cyan('●')} {$label}  "
                : "  {$this->dim('○')} {$this->dim($label)}  "
            )
            ->map(fn ($label) => $this->pad($label, $width))
            ->map(fn ($label, $i) => match (true) {
                $i === $lines->keys()->first() && $prompt->hasLabelsAbove() => preg_replace('/\s$/', $this->cyan('↑'), $label),
                $i === $lines->keys()->last() && $prompt->hasLabelsBelow() => preg_replace('/\s$/', $this->cyan('↓'), $label),
                default => $label,
            })
            ->implode(PHP_EOL);
    }
}
