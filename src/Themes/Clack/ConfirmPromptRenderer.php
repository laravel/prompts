<?php

namespace Laravel\Prompts\Themes\Clack;

use Laravel\Prompts\Concerns\Colors;
use Laravel\Prompts\ConfirmPrompt;

class ConfirmPromptRenderer
{
    use Colors;

    public function __invoke(ConfirmPrompt $prompt)
    {
        return match ($prompt->state) {
            'submit' => <<<EOT
                {$this->gray('│')}
                {$this->green('◇')}  {$prompt->message}
                {$this->gray('│')}  {$this->dim($prompt->confirmed ? 'Yes' : 'No')}

                EOT,

            'cancel' => <<<EOT
                {$this->gray('│')}
                {$this->red('■')}  {$prompt->message}
                {$this->gray('│')}  {$this->strikethrough($this->dim($prompt->confirmed ? 'Yes' : 'No'))}
                {$this->gray('└')}  {$this->red('Operation cancelled. ')}

                EOT,

            default => <<<EOT
                {$this->gray('│')}
                {$this->cyan('◆')}  {$prompt->message}
                {$this->cyan('│')}  {$this->renderOptions($prompt)}
                {$this->cyan('└')}

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
