<?php

namespace Laravel\Prompts\Themes\Default;

use Laravel\Prompts\Concerns\Colors;
use Laravel\Prompts\MultiSelectPrompt;

class MultiSelectPromptRenderer
{
    use Colors;
    use Concerns\DrawsBoxes;
    use Concerns\DrawsScrollbars;

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
        return $this->scroll(
            collect($prompt->options)
                ->values()
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
                }),
            $prompt->highlighted,
            $prompt->scroll,
            $this->longest($prompt->options, padding: 6)
        )->implode(PHP_EOL);
    }

    /**
     * Render the selected options.
     */
    protected function renderSelectedOptions(MultiSelectPrompt $prompt): string
    {
        return implode(', ', $prompt->labels());
    }
}
