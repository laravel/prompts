<?php

namespace Laravel\Prompts\Themes\Default;

use Laravel\Prompts\Concerns\HasSpinner;
use Laravel\Prompts\ProcessLog;

class ProcessLogRenderer extends Renderer
{
    use HasSpinner;

    /**
     * Render the process log.
     */
    public function __invoke(ProcessLog $processLog): string
    {
        if ($processLog->static) {
            return $this->line(" {$this->cyan($this->staticFrame)} {$processLog->label}");
        }

        $processLog->interval = $this->interval;

        $this->line(" {$this->cyan($this->spinnerFrame($processLog->count))} {$processLog->label}");

        $leadPadding = str_repeat(' ', 3);

        $stableMessages = array_slice($processLog->stableMessages, -$processLog->maxStableMessages);

        foreach ($stableMessages as $stableMessage) {
            $symbol = match ($stableMessage['type']) {
                'success' => $this->green('✔'),
                'error' => $this->red('✘'),
                'warning' => $this->yellow('⚠'),
                default => '',
            };

            $this->line($leadPadding.$symbol.' '.$stableMessage['message']);
        }

        if (count($processLog->stableMessages) > 0 || count($processLog->logs) > 0) {
            $this->line($this->gray(' '.str_repeat('─', $this->prompt->terminal()->cols() - 10)));
        } else {
            $this->newLine();
        }

        $logs = array_slice($processLog->logs, -$processLog->limit);

        foreach ($logs as $log) {
            $this->line(' '.$this->dim($log));
        }

        $remaining = $processLog->limit - count($processLog->logs);

        while ($remaining > 0) {
            $this->line('');
            $remaining--;
        }

        return $this;
    }
}
