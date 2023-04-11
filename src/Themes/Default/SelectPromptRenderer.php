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

                {$this->box($this->dim($prompt->label), $this->dim($prompt->label()))}

                EOT,

            'cancel' => <<<EOT

                {$this->box($prompt->label, $this->renderOptions($prompt), color: 'red')}
                {$this->red('  ⚠ Cancelled.')}

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
                    ? "{$this->cyan('›')} {$this->cyan('●')} {$label}  "
                    : "  {$this->dim('○')} {$this->dim($label)}  "
                ),
            $prompt->highlighted,
            $prompt->scroll,
            $this->longest($prompt->options, padding: 6)
        )->implode(PHP_EOL);
    }
}
