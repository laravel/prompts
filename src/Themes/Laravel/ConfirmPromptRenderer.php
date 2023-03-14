<?php

namespace Laravel\Prompts\Themes\Laravel;

use Laravel\Prompts\Concerns\Colors;
use Laravel\Prompts\ConfirmPrompt;

class ConfirmPromptRenderer
{
    use Colors;

    public function __invoke(ConfirmPrompt $prompt)
    {
        return match ($prompt->state) {
            'submit' => <<<EOT

                {$this->gray(' ┌')}  {$prompt->message}
                {$this->gray(' └')}  {$this->dim($prompt->confirmed ? 'Yes' : 'No')}

                EOT,

            'cancel' => <<<EOT

                {$this->red(' ┌')}  {$prompt->message}
                {$this->red(' └')}  {$this->strikethrough($this->dim($prompt->confirmed ? 'Yes' : 'No'))}
                {$this->red(' ⚠')}  {$this->red('Operation cancelled. ')}

                EOT,

            default => <<<EOT

                {$this->gray(' ┏')}  {$prompt->message}
                {$this->gray(' ┗')}  {$this->renderOptions($prompt)}


                EOT,
        };
    }

    protected function renderOptions($prompt)
    {
        return $prompt->confirmed
            ? "{$this->green('●')} Yes {$this->dim('/ ○ No')}"
            : "{$this->dim('○ Yes /')} {$this->green('●')} No";
    }
}
