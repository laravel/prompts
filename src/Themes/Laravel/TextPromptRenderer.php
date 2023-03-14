<?php

namespace Laravel\Prompts\Themes\Laravel;

use Laravel\Prompts\Concerns\Colors;
use Laravel\Prompts\TextPrompt;

class TextPromptRenderer
{
    use Colors;

    public function __invoke(TextPrompt $prompt)
    {
        return match ($prompt->state) {
            'error' => <<<EOT
                {$this->yellow(' ┏')}  {$prompt->message}
                {$this->yellow(' ┗')}  {$prompt->valueWithCursor()}
                {$this->yellow(' ⚠')}  {$this->yellow($prompt->error)}

                EOT,

            'submit' => <<<EOT
                {$this->gray(' ┌')}  {$prompt->message}
                {$this->gray(' └')}  {$this->dim($prompt->value())}

                EOT,

            'cancel' => <<<EOT
                {$this->red(' ┌')}  {$prompt->message}
                {$this->red(' └')}  {$this->strikethrough($this->dim($prompt->value() ?? $prompt->placeholder))}
                {$this->red(' ⚠')}  {$this->red('Cancelled.')}

                EOT,

            default => <<<EOT
                {$this->gray(' ┏')}  {$prompt->message}
                {$this->gray(' ┗')}  {$prompt->valueWithCursor()}


                EOT,
        };
    }
}
