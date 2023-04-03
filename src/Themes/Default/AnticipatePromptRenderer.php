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

                {$this->box($this->cyan($prompt->message), $this->valueWithCursorAndArrow($prompt), $this->renderOptions($prompt))}
                {$this->spacer($prompt)}

                EOT,
        };
    }

    protected function valueWithCursorAndArrow(AnticipatePrompt $prompt): string
    {
        if ($prompt->highlighted !== null || $prompt->value() !== '') {
            return $prompt->valueWithCursor();
        }

        return preg_replace(
            '/\s$/',
            $this->cyan('↓'),
            $this->pad($prompt->valueWithCursor(). '  ', $this->longest($prompt->matches(), padding: 2))
        );
    }

    /**
     * Render a spacer to prevent jumping when the suggestions are displayed.
     */
    protected function spacer(AnticipatePrompt $prompt): string
    {
        if ($prompt->value() === '' && $prompt->highlighted === null) {
            return str_repeat(PHP_EOL, $prompt->scroll() + 1);
        }

        return '';
    }

    /**
     * Render the options.
     */
    protected function renderOptions(AnticipatePrompt $prompt): string
    {
        $width = $this->longest($prompt->matches(), padding: 4);

        $lines = collect($prompt->scrolledMatches());

        if ($lines->isEmpty() || ($prompt->value() === '' && $prompt->highlighted === null)) {
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
