<?php

namespace Laravel\Prompts\Themes\Default;

use Laravel\Prompts\Concerns\Colors;
use Laravel\Prompts\MultiSelectPrompt;

class MultiSelectPromptRenderer
{
    use Colors;
    use Concerns\DrawsBoxes;

    /**
     * Render the multiselect prompt.
     */
    public function __invoke(MultiSelectPrompt $prompt): string
    {
        return match ($prompt->state) {
            'submit' => <<<EOT

                {$this->box($this->dim($prompt->message), $this->dim($this->renderSelectedOptions($prompt)))}

                EOT,

            'cancel' => <<<EOT

                {$this->box($prompt->message, $this->strikethrough($this->dim($this->renderSelectedOptions($prompt))), color: 'red')}
                {$this->red('  ⚠ Cancelled.')}

                EOT,

            'error' => <<<EOT

                {$this->box($prompt->message, $this->renderOptions($prompt), color: 'yellow')}
                {$this->yellow("  ⚠ {$prompt->error}")}

                EOT,

            default => <<<EOT

                {$this->box($this->cyan($prompt->message), $this->renderOptions($prompt))}


                EOT,
        };
    }

    /**
     * Render the options.
     */
    protected function renderOptions(MultiSelectPrompt $prompt): string
    {
        $width = $this->longest($prompt->options, padding: 6);

        $lines = collect($prompt->scrolledLabels());

        return $lines
            ->map(function ($label, $key) use ($prompt) {
                $active = $prompt->isHighlighted(array_is_list($prompt->options) ? $label : $key);
                $selected = $prompt->isSelected(array_is_list($prompt->options) ? $label : $key);

                return match (true) {
                    $active && $selected => "{$this->cyan('› ◼')} {$label}  ",
                    $active => "{$this->cyan('›')} ◻ {$label}  ",
                    $selected => "  {$this->cyan('◼')} {$this->dim($label)}  ",
                    default => "  {$this->dim('◻')} {$this->dim($label)}  ",
                };
            })
            ->map(fn ($label) => $this->pad($label, $width))
            ->map(fn ($label, $key) => match (true) {
                $key === $lines->keys()->first() && $prompt->hasLabelsAbove() => preg_replace('/\s$/', $this->cyan('↑'), $label),
                $key === $lines->keys()->last() && $prompt->hasLabelsBelow() => preg_replace('/\s$/', $this->cyan('↓'), $label),
                default => $label,
            })
            ->implode(PHP_EOL);
    }

    /**
     * Render the selected options.
     */
    protected function renderSelectedOptions(MultiSelectPrompt $prompt): string
    {
        return implode(', ', $prompt->labels());
    }
}
