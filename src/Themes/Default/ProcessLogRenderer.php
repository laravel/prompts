<?php

namespace Laravel\Prompts\Themes\Default;

use Laravel\Prompts\ProcessLog;

class ProcessLogRenderer extends Renderer
{
    /**
     * The frames of the spinner (single dot moving around the perimeter).
     *
     * @var array<string>
     */
    protected array $frames = ['⠂', '⠒', '⠐', '⠰', '⠠', '⠤', '⠄', '⠆'];

    /**
     * The frame to render when the spinner is static.
     */
    protected string $staticFrame = '⠶';

    /**
     * The interval between frames.
     */
    protected int $interval = 75;

    /**
     * Render the spinner.
     */
    public function __invoke(ProcessLog $processLog): string
    {
        if ($processLog->static) {
            return $this->line(" {$this->cyan($this->staticFrame)} {$processLog->message}");
        }

        $processLog->interval = $this->interval;

        $frame = $this->frames[$processLog->count % count($this->frames)];

        $this->line(" {$this->cyan($frame)} {$processLog->message}");

        $leadPadding = str_repeat(' ', 3);

        $stableMessages = array_slice($processLog->stableMessages, -$processLog->maxStableMessages);

        foreach ($stableMessages as $stableMessage) {
            $symbol = match ($stableMessage['type']) {
                'success' => $this->green('✔'),
                'error' => $this->red('✘'),
                'warning' => $this->yellow('⚠'),
                default => '',
            };

            $this->line($leadPadding . $symbol . ' ' . $stableMessage['message']);
        }

        if (count($processLog->stableMessages) > 0 || count($processLog->logs) > 0) {
            $this->line($this->gray(' ' . str_repeat('─', $this->prompt->terminal()->cols() - 10)));
        } else {
            $this->newLine();
        }

        $logs = array_slice($processLog->logs, -$processLog->limit);

        foreach ($logs as $log) {
            $this->line(' ' . $this->dim($log));
        }

        $remaining = $processLog->limit - count($processLog->logs);

        while ($remaining > 0) {
            $this->line('');
            $remaining--;
        }

        return $this;
    }
}
