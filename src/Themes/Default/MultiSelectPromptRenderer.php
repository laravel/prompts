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
            ->map(function ($label, $index) use ($prompt) {
                $active = $index === $prompt->highlighted;
                if (array_is_list($prompt->options)) {
                    $value = $prompt->options[$index];
                } else {
                    $value = array_keys($prompt->options)[$index];
                }
                $selected = in_array($value, $prompt->value());

                return match (true) {
                    $active && $selected => "{$this->cyan('› ◼')} {$label}  ",
                    $active => "{$this->cyan('›')} ◻ {$label}  ",
                    $selected => "  {$this->cyan('◼')} {$this->dim($label)}  ",
                    default => "  {$this->dim('◻')} {$this->dim($label)}  ",
                };
            })
            ->map(fn ($label) => $this->pad($label, $width))
            ->when(
                count($prompt->options) > $prompt->scroll(),
                fn ($lines) => $lines->map(fn ($label, $i) => match (true) {
                    $i === $this->scrollPosition($prompt) => preg_replace('/\s$/', $this->cyan('┃'), $label),
                    default => preg_replace('/\s$/', $this->gray('│'), $label),
                })
            )
            ->implode(PHP_EOL);
    }

    protected function scrollPosition(MultiSelectPrompt $prompt)
    {
        $highlighted = $prompt->highlighted;

        if ($highlighted < $prompt->scroll()) {
            return 0;
        }

        if ($highlighted === count($prompt->options) - 1) {
            return count($prompt->options) - 1;
        }

        $count = count($prompt->options);

        $percent = ($highlighted + 1 - $prompt->scroll()) / ($count - $prompt->scroll());

        $keys = array_keys(array_slice($prompt->scrolledLabels(), 1, -1, true));
        $position = (int) ceil($percent * count($keys) - 1);

        return $keys[$position];
    }

    /**
     * Render the selected options.
     */
    protected function renderSelectedOptions(MultiSelectPrompt $prompt): string
    {
        return implode(', ', $prompt->labels());
    }
}
