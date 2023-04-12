<?php

namespace Laravel\Prompts\Themes\Default;

use Laravel\Prompts\Concerns\Colors;
use Laravel\Prompts\SelectPrompt;

class SelectPromptRenderer
{
    use Colors;
    use Concerns\DrawsBoxes;
    use Concerns\DrawsScrollbars;

    /**
     * Render the select prompt.
     */
    public function __invoke(SelectPrompt $prompt): string
    {
        return match ($prompt->state) {
            'submit' => <<<EOT

                {$this->box($this->dim($prompt->label), $this->dim($this->format($prompt->label())))}

                EOT,

            'cancel' => <<<EOT

                {$this->box($prompt->label, $this->renderOptions($prompt), color: 'red')}
                {$this->red('  ⚠ Cancelled.')}

                EOT,

            'error' => <<<EOT

                {$this->box($prompt->label, $this->renderOptions($prompt), color: 'yellow')}
                {$this->yellow("  ⚠ {$prompt->error}")}

                EOT,

            default => <<<EOT

                {$this->box($this->cyan($prompt->label), $this->renderOptions($prompt))}


                EOT,
        };
    }

    /**
     * Render the options.
     */
    protected function renderOptions(SelectPrompt $prompt): string
    {
        return $this->scroll(
            collect($prompt->options)
                ->values()
                ->map(fn ($label, $i) => $prompt->highlighted === $i
                    ? "{$this->cyan('›')} {$this->cyan('●')} {$this->format($label)}  "
                    : "  {$this->dim('○')} {$this->dim($this->format($label))}  "
                ),
            $prompt->highlighted,
            $prompt->scroll,
            $this->longest($prompt->options, padding: 6)
        )->implode(PHP_EOL);
    }
}
