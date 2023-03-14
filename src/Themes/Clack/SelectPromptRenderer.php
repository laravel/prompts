<?php

namespace Laravel\Prompts\Themes\Clack;

use Laravel\Prompts\Concerns\Colors;
use Laravel\Prompts\SelectPrompt;

class SelectPromptRenderer
{
    use Colors;

    public function __invoke(SelectPrompt $prompt)
    {
        return match ($prompt->state) {
            'submit' => <<<EOT
                {$this->gray('│')}
                {$this->green('◇')}  {$prompt->message}
                {$this->gray('│')}  {$this->dim($prompt->label())}

                EOT,

            'cancel' => <<<EOT
                {$this->gray('│')}
                {$this->red('■')}  {$prompt->message}
                {$this->gray('│')}  {$this->strikethrough($this->dim($prompt->label()))}
                {$this->gray('└')}  {$this->red('Operation cancelled. ')}

                EOT,

            default => <<<EOT
                {$this->gray('│')}
                {$this->cyan('◆')}  {$prompt->message}
                {$this->renderOptions($prompt)}
                {$this->cyan('└')}

                EOT,
        };
    }

    protected function renderOptions($prompt)
    {
        return collect($prompt->options)
            ->values()
            ->map(fn ($label, $i) => $prompt->highlighted === $i
                ? "{$this->cyan('│')}  {$this->green('●')} {$label}"
                : "{$this->cyan('│')}  {$this->dim('○')} {$this->dim($label)}"
            )
            ->implode(PHP_EOL);
    }
}
