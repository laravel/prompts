<?php

namespace Laravel\Prompts\Themes\Default\Concerns;

use Laravel\Prompts\DatePickerPrompt;
use Laravel\Prompts\TimePickerPrompt;

trait RendersSegments
{
    protected function inputRow(DatePickerPrompt|TimePickerPrompt $prompt): string
    {
        $segments = $prompt->segmentValues();

        if (isset($segments[$prompt->focused])) {
            $segments[$prompt->focused] = $this->inverse($segments[$prompt->focused]);
        }

        return $prompt->formatSegments($segments);
    }
}
