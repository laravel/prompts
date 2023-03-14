<?php

namespace Laravel\Prompts\Themes\Clack;

use Laravel\Prompts\Concerns\Colors;
use Laravel\Prompts\MultiSelectPrompt;

class MultiSelectPromptRenderer
{
    use Colors;

    public function __invoke(MultiSelectPrompt $prompt)
    {
        return match ($prompt->state) {
            'submit' => <<<EOT
                {$this->gray('│')}
                {$this->green('◇')}  {$prompt->message}
                {$this->gray('│')}  {$this->dim($this->selected($prompt))}

                EOT,

            'cancel' => <<<EOT
                {$this->gray('│')}
                {$this->red('■')}  {$prompt->message}
                {$this->gray('│')}  {$this->strikethrough($this->dim($this->selected($prompt)))}
                {$this->gray('└')}  {$this->red('Operation cancelled. ')}

                EOT,

            'error' => <<<EOT
                {$this->gray('│')}
                {$this->yellow('▲')}  {$prompt->message}
                {$this->renderOptions($prompt, $this->yellow('│'))}
                {$this->yellow('└')}  {$this->yellow($prompt->error)}

                EOT,

            default => <<<EOT
                {$this->gray('│')}
                {$this->cyan('◆')}  {$prompt->message}
                {$this->renderOptions($prompt, $this->cyan('│'))}
                {$this->cyan('└')}

                EOT,
        };
    }

    protected function selected($prompt)
    {
        return collect($prompt->options)
            ->filter(fn ($label, $key) => in_array($key, $prompt->values))
            ->implode(', ');
    }

    protected function renderOptions($prompt, $prefix)
    {
        return collect($prompt->options)
            ->values()
            ->map(function ($label, $i) use ($prompt, $prefix) {
                $selected = in_array(array_keys($prompt->options)[$i], $prompt->value());
                $active = $prompt->highlighted === $i;

                return match (true) {
                    $active && $selected => "{$prefix}  {$this->green('◼')} {$label}",
                    $active => "{$prefix}  {$this->cyan('◻')} {$label}",
                    $selected => "{$prefix}  {$this->green('◼')} {$this->dim($label)}",
                    default => "{$prefix}  {$this->dim('◻')} {$this->dim($label)}",
                };
            })
            ->implode(PHP_EOL);
    }
}
