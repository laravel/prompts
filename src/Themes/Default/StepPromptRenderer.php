<?php

namespace Laravel\Prompts\Themes\Default;

use Laravel\Prompts\StepPrompt;

class StepPromptRenderer extends Renderer
{
    public function __invoke(StepPrompt $prompt): string
    {
        return match ($prompt->state) {
            'active' => $this->line(sprintf(' %s %d%s%d',
                $this->dim($prompt->title),
                $prompt->currentStepNumber(),
                $this->dim('/'),
                $prompt->totalSteps()),
            ),
            'reverting' => $this->dim(' Reverting step…'),
            'error' => $this->error("Step {$prompt->currentStepNumber()} cannot be reverted."),
            default => '',
        };
    }
}
