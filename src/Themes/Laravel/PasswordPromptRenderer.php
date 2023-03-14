<?php

namespace Laravel\Prompts\Themes\Laravel;

use Laravel\Prompts\Concerns\Colors;
use Laravel\Prompts\PasswordPrompt;

class PasswordPromptRenderer
{
    use Colors;

    public function __invoke(PasswordPrompt $prompt)
    {
        return match ($prompt->state) {
            'error' => <<<EOT

                {$this->yellow(' ┏')}  {$prompt->message}
                {$this->yellow(' ┗')}  {$prompt->maskedWithCursor()}
                {$this->yellow(' ⚠')}  {$this->yellow($prompt->error)}

                EOT,

            'submit' => <<<EOT

                {$this->gray(' ┌')}  {$prompt->message}
                {$this->gray(' └')}  {$this->dim($prompt->masked())}

                EOT,

            'cancel' => <<<EOT

                {$this->red(' ┌')}  {$prompt->message}
                {$this->red(' └')}  {$this->strikethrough($this->dim($prompt->masked()))}
                {$this->red(' ⚠')}  {$this->red('Operation cancelled. ')}

                EOT,

            default => <<<EOT

                {$this->gray(' ┏')}  {$prompt->message}
                {$this->gray(' ┗')}  {$prompt->maskedWithCursor()}


                EOT,
        };
    }
}
