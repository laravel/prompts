<?php

namespace Laravel\Prompts\Themes\Clack;

use Laravel\Prompts\Concerns\Colors;
use Laravel\Prompts\PasswordPrompt;

class PasswordPromptRenderer
{
    use Colors;

    public function __invoke(PasswordPrompt $prompt)
    {
        return match ($prompt->state) {
            'error' => <<<EOT
                {$this->gray('│')}
                {$this->yellow('▲')}  {$prompt->message}
                {$this->yellow('│')}  {$prompt->maskedWithCursor()}
                {$this->yellow('└')}  {$this->yellow($prompt->error)}

                EOT,

            'submit' => <<<EOT
                {$this->gray('│')}
                {$this->green('◇')}  {$prompt->message}
                {$this->gray('│')}  {$this->dim($prompt->masked())}

                EOT,

            'cancel' => <<<EOT
                {$this->gray('│')}
                {$this->red('■')}  {$prompt->message}
                {$this->gray('│')}  {$this->strikethrough($this->dim($prompt->masked()))}
                {$this->gray('└')}  {$this->red('Operation cancelled. ')}

                EOT,

            default => <<<EOT
                {$this->gray('│')}
                {$this->cyan('◆')}  {$prompt->message}
                {$this->cyan('│')}  {$prompt->maskedWithCursor()}
                {$this->cyan('└')}

                EOT,
        };
    }
}
