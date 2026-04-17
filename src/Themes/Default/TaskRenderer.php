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

        $leadPadding = str_repeat(' ', 3);

        $stableMessages = array_slice($task->stableMessages, -$task->maxStableMessages);

        if ($task->finished && $task->keepSummary && count($stableMessages) > 0) {
            $this->line(" {$this->cyan('•')} {$task->label}");

            foreach ($stableMessages as $stableMessage) {
                $this->line($leadPadding.$this->stableMessageSymbol($stableMessage['type']).' '.$stableMessage['message']);
            }

            $this->newLine();

            return $this;
        }

        $this->line(" {$this->cyan($this->spinnerFrame($task->count))} {$task->label}");

        foreach ($stableMessages as $stableMessage) {
            $this->line($leadPadding.$this->stableMessageSymbol($stableMessage['type']).' '.$stableMessage['message']);
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

    protected function stableMessageSymbol(string $type): string
    {
        return match ($type) {
            'success' => $this->green('✔'),
            'error' => $this->red('✘'),
            'warning' => $this->yellow('⚠'),
            default => '',
        };
    }
}
