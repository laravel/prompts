<?php

namespace Laravel\Prompts\Themes\Laravel;

use Laravel\Prompts\Concerns\Colors;
use Laravel\Prompts\SelectPrompt;

class SelectPromptRenderer
{
    use Colors;

    public function __invoke(SelectPrompt $prompt)
    {
        return match ($prompt->state) {
            'submit' => <<<EOT

                {$this->gray(' ┌')}  {$prompt->message}
                {$this->gray(' └')}  {$this->dim($prompt->label())}

                EOT,

            'cancel' => <<<EOT

                {$this->red(' ┌')}  {$prompt->message}
                {$this->red(' └')}  {$this->strikethrough($this->dim($prompt->label()))}
                {$this->red(' ⚠')}  {$this->red('Operation cancelled. ')}

                EOT,

            default => <<<EOT

                {$this->gray(' ┏')}  {$prompt->message}
                {$this->renderOptions($prompt)}


                EOT,
        };
    }

    protected function renderOptions($prompt)
    {
        return collect($prompt->options)
            ->values()
            ->map(fn ($label, $i) => $prompt->highlighted === $i
                ? " {$this->gray($i === count($prompt->options) - 1 ? '┗' : '┃')}  {$this->green('●')} {$label}"
                : " {$this->gray($i === count($prompt->options) - 1 ? '┗' : '┃')}  {$this->dim('○')} {$this->dim($label)}"
            )
            ->implode(PHP_EOL);
    }
}
