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
            ->when(
                count($prompt->options) > $prompt->scroll(),
                fn ($lines) => $lines->map(fn ($label, $i) => match (true) {
                    $i === $this->scrollPosition($prompt) => preg_replace('/\s$/', $this->cyan('┃'), $label),
                    default => preg_replace('/\s$/', $this->gray('│'), $label),
                })
            )
            ->implode(PHP_EOL);
    }

    protected function scrollPosition(SelectPrompt $prompt)
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
}
