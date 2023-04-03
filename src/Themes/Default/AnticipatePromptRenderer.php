<?php

namespace Laravel\Prompts\Themes\Default;

use Laravel\Prompts\AnticipatePrompt;
use Laravel\Prompts\Concerns\Colors;

class AnticipatePromptRenderer
{
    use Colors;
    use Concerns\DrawsBoxes;

    /**
     * Render the anticipate prompt.
     */
    public function __invoke(AnticipatePrompt $prompt): string
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

                {$this->box($this->cyan($prompt->message), $prompt->valueWithCursor(), $this->renderOptions($prompt))}


                EOT,
        };
    }

    /**
     * Render the options.
     */
    protected function renderOptions(AnticipatePrompt $prompt): string
    {
        $width = $this->longest($prompt->matches(), padding: 4);

        $lines = collect($prompt->scrolledMatches());

        if ($lines->isEmpty()) {
            return '';
        }

        return $lines
            ->map(fn ($label, $i) => $prompt->highlighted === $i
                ? "{$this->cyan('›')} {$label}  "
                : "  {$this->dim($label)}  "
            )
            ->map(fn ($label) => $this->pad($label, $width))
            ->map(fn ($label, $i) => match (true) {
                $i === $lines->keys()->first() && $prompt->hasMatchesAbove() => preg_replace('/\s$/', $this->cyan('↑'), $label),
                $i === $lines->keys()->last() && $prompt->hasMatchesBelow() => preg_replace('/\s$/', $this->cyan('↓'), $label),
                default => $label,
            })
            ->implode(PHP_EOL);
    }
}
