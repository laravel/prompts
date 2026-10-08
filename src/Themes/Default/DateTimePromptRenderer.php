<?php

namespace Laravel\Prompts\Themes\Default;

use Laravel\Prompts\DatePrompt;
use Laravel\Prompts\DateTimePrompt;

class DateTimePromptRenderer extends DatePromptRenderer
{
    /**
     * Render the selected date, the calendar grid, and the time row.
     */
    protected function renderBody(DatePrompt $prompt): string
    {
        if (! $prompt->calendar) {
            return parent::renderBody($prompt);
        }

        /** @var DateTimePrompt $prompt */
        return implode(PHP_EOL, [
            parent::renderBody($prompt),
            '',
            $this->timeRow($prompt),
        ]);
    }

    /**
     * Render the time segments, highlighting the focused one.
     */
    protected function timeRow(DateTimePrompt $prompt): string
    {
        $segments = array_slice($prompt->segmentValues(), 3);

        if (isset($segments[$prompt->focused])) {
            $segments[$prompt->focused] = $this->inverse($segments[$prompt->focused]);
        }

        return $this->dim('Time').'  '.implode(':', $segments);
    }
}
