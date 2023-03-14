<?php

namespace Laravel\Prompts\Themes\Clack;

use Laravel\Prompts\Concerns\Colors;
use Laravel\Prompts\TextPrompt;

class TextPromptRenderer
{
    use Colors;

    public function __invoke(TextPrompt $prompt)
    {
        return match ($prompt->state) {
            'error' => <<<EOT
                {$this->gray('│')}
                {$this->yellow('▲')}  {$prompt->message}
                {$this->yellow('│')}  {$prompt->valueWithCursor()}
                {$this->yellow('└')}  {$this->yellow($prompt->error)}

                EOT,

            'submit' => <<<EOT
                {$this->gray('│')}
                {$this->green('◇')}  {$prompt->message}
                {$this->gray('│')}  {$this->dim($prompt->value())}

                EOT,

            'cancel' => <<<EOT
                {$this->gray('│')}
                {$this->red('■')}  {$prompt->message}
                {$this->gray('│')}  {$this->strikethrough($this->dim($prompt->value()))}
                {$this->gray('└')}  {$this->red('Operation cancelled. ')}

                EOT,

            default => <<<EOT
                {$this->gray('│')}
                {$this->cyan('◆')}  {$prompt->message}
                {$this->cyan('│')}  {$prompt->valueWithCursor()}
                {$this->cyan('└')}

                EOT,
        };
    }
}
