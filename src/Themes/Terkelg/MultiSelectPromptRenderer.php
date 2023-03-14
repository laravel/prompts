<?php

namespace Laravel\Prompts\Themes\Terkelg;

use Laravel\Prompts\Concerns\Colors;
use Laravel\Prompts\MultiSelectPrompt;

class MultiSelectPromptRenderer
{
    use Colors;

    public function __invoke(MultiSelectPrompt $prompt)
    {
        return match ($prompt->state) {
            'submit' => <<<EOT
                {$this->green('✔')} {$this->bold($prompt->message)} {$this->dim('…')} {$this->selected($prompt)}

                EOT,

            'cancel' => <<<EOT
                {$this->red('✖')} {$this->bold($prompt->message)} {$this->dim('…')} {$this->selected($prompt)}

                EOT,

            'error' => <<<EOT
                {$this->cyan('?')} {$this->bold($prompt->message)} {$this->dim('›')}
                {$this->renderOptions($prompt)}
                {$this->dim('›')} {$this->red($this->italic($prompt->error))}

                EOT,

            default => <<<EOT
                {$this->cyan('?')} {$this->bold($prompt->message)} {$this->dim('›')}
                {$this->renderOptions($prompt)}

                EOT,
        };
    }

    protected function selected($prompt)
    {
        return collect($prompt->options)
            ->filter(fn ($label, $key) => in_array($key, $prompt->values))
            ->implode(', ');
    }

    protected function renderOptions($prompt)
    {
        return collect($prompt->options)
            ->values()
            ->map(function ($label, $i) use ($prompt) {
                $selected = in_array(array_keys($prompt->options)[$i], $prompt->value());
                $active = $prompt->highlighted === $i;

                $icon = $selected ? $this->green('◉') : '○';
                $text = $active ? $this->underline($this->cyan($label)) : $label;

                return "{$icon}   {$text}";
            })
            ->implode(PHP_EOL);
    }
}
