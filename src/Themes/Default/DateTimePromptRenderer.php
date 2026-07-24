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
        assert($prompt instanceof DateTimePrompt);

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
        $segments = [
            'hour' => sprintf('%02d', $prompt->hour),
            'minute' => sprintf('%02d', $prompt->minute),
        ];

        if ($prompt->withSeconds) {
            $segments['second'] = sprintf('%02d', $prompt->second);
        }

        if (isset($segments[$prompt->focused])) {
            $segments[$prompt->focused] = $this->inverse($segments[$prompt->focused]);
        }

        return $this->dim('Time').'  '.implode(':', $segments);
    }
}
