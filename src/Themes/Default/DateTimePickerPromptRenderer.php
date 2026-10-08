<?php

namespace Laravel\Prompts\Themes\Default;

use Laravel\Prompts\DatePickerPrompt;
use Laravel\Prompts\DateTimePickerPrompt;

class DateTimePickerPromptRenderer extends DatePickerPromptRenderer
{
    /**
     * Render the selected date, the calendar grid, and the time row.
     */
    protected function renderBody(DatePickerPrompt $prompt): string
    {
        if (! $prompt->calendar) {
            return parent::renderBody($prompt);
        }

        /** @var DateTimePickerPrompt $prompt */
        return implode(PHP_EOL, [
            parent::renderBody($prompt),
            '',
            $this->timeRow($prompt),
        ]);
    }

    /**
     * Render the time segments, highlighting the focused one.
     */
    protected function timeRow(DateTimePickerPrompt $prompt): string
    {
        $segments = array_slice($prompt->segmentValues(), 3);

        if (isset($segments[$prompt->focused])) {
            $segments[$prompt->focused] = $this->inverse($segments[$prompt->focused]);
        }

        return $this->dim('Time').'  '.$prompt->formatTimeSegments($segments);
    }
}
