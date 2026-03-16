<?php

namespace Laravel\Prompts\Themes\Default;

use Laravel\Prompts\Concerns\HasSpinner;
use Laravel\Prompts\Task;

class TaskRenderer extends Renderer
{
    use HasSpinner;

    /**
     * Render the task.
     */
    public function __invoke(Task $task): string
    {
        if ($task->static) {
            return $this->line(" {$this->cyan($this->staticFrame)} {$task->label}");
        }

        $task->interval = $this->interval;

        $this->line(" {$this->cyan($this->spinnerFrame($task->count))} {$task->label}");

        $leadPadding = str_repeat(' ', 3);

        $stableMessages = array_slice($task->stableMessages, -$task->maxStableMessages);

        foreach ($stableMessages as $stableMessage) {
            $symbol = match ($stableMessage['type']) {
                'success' => $this->green('✔'),
                'error' => $this->red('✘'),
                'warning' => $this->yellow('⚠'),
                default => '',
            };

            $this->line($leadPadding.$symbol.' '.$stableMessage['message']);
        }

        if (count($task->stableMessages) > 0 || count($task->logs) > 0) {
            $this->line($this->gray(' '.str_repeat('─', $this->prompt->terminal()->cols() - 10)));
        } else {
            $this->newLine();
        }

        $logs = array_slice($task->logs, -$task->limit);

        foreach ($logs as $log) {
            $this->line(' '.$this->dim($log));
        }

        $remaining = $task->limit - count($task->logs);

        while ($remaining > 0) {
            $this->line('');
            $remaining--;
        }

        return $this;
    }
}
