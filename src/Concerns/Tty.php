<?php

namespace Laravel\Prompts\Concerns;

trait Tty
{
    /**
     * The initial TTY mode.
     */
    protected string $initialTtyMode;

    /**
     * Set the TTY mode.
     */
    protected function setTty(string $mode): void
    {
        $this->initialTtyMode ??= shell_exec('stty -g');

        shell_exec("stty $mode");
    }

    /**
     * Restore the initial TTY mode.
     */
    protected function restoreTty(): void
    {
        if ($this->initialTtyMode) {
            shell_exec("stty {$this->initialTtyMode}");
        }
    }
}
