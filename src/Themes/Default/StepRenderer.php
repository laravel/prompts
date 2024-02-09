<?php

namespace Laravel\Prompts\Themes\Default;

use Laravel\Prompts\Note;
use Laravel\Prompts\Step;

class StepRenderer extends Renderer
{
    /**
     * Render the note.
     */
    public function __invoke(Step $step): string
    {
        $this->hint("{$step->title} {$step->currentStep} of {$step->totalSteps}");

        return $this;
    }
}
