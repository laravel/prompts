<?php

namespace Laravel\Prompts\Themes\Default\Concerns;

use Laravel\Prompts\TimeBasedPrompt;

trait RendersSegments
{
    protected function inputRow(TimeBasedPrompt $prompt): string
    {
        $segments = $prompt->segmentValues();

        if (isset($segments[$prompt->focused])) {
            $segments[$prompt->focused] = $this->inverse($segments[$prompt->focused]);
        }

        return $prompt->formatSegments($segments);
    }
}
