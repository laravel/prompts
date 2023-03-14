<?php

namespace Laravel\Prompts\Themes\Laravel;

use Laravel\Prompts\Concerns\Colors;
use Laravel\Prompts\MultiSelectPrompt;

class MultiSelectPromptRenderer
{
    use Colors;

    public function __invoke(MultiSelectPrompt $prompt)
    {
        return match ($prompt->state) {
            'submit' => <<<EOT

                {$this->gray(' ┌')}  {$prompt->message}
                {$this->gray(' └')}  {$this->dim($this->selected($prompt))}

                EOT,

            'cancel' => <<<EOT

                {$this->red(' ┌')}  {$prompt->message}
                {$this->red(' └')}  {$this->strikethrough($this->dim($this->selected($prompt)))}
                {$this->red(' ⚠')}  {$this->red('Operation cancelled. ')}

                EOT,

            'error' => <<<EOT

                {$this->yellow(' ┏')}  {$prompt->message}
                {$this->renderOptions($prompt, 'yellow')}
                {$this->yellow(' ⚠')}  {$this->yellow($prompt->error)}

                EOT,

            default => <<<EOT

                {$this->gray(' ┏')}  {$prompt->message}
                {$this->renderOptions($prompt, 'gray')}


                EOT,
        };
    }

    protected function selected($prompt)
    {
        return collect($prompt->options)
            ->filter(fn ($label, $key) => in_array($key, $prompt->values))
            ->implode(', ');
    }

    protected function renderOptions($prompt, $borderColor)
    {
        return collect($prompt->options)
            ->values()
            ->map(function ($label, $i) use ($prompt, $borderColor) {
                $selected = in_array(array_keys($prompt->options)[$i], $prompt->value());
                $active = $prompt->highlighted === $i;

                $border = $this->{$borderColor}($i === count($prompt->options) - 1 ? '┗' : '┃');

                return match (true) {
                    $active && $selected => " {$border}  {$this->green('◼')} {$label}",
                    $active => " {$border}  {$this->cyan('◻')} {$label}",
                    $selected => " {$border}  {$this->green('◼')} {$this->dim($label)}",
                    default => " {$border}  {$this->dim('◻')} {$this->dim($label)}",
                };
            })
            ->implode(PHP_EOL);
    }
}
