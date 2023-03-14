<?php

namespace Laravel\Prompts\Concerns;

trait Tty
{
    /**
     * The initial TTY mode.
     *
     * @var string|null
     */
    protected $initialTtyMode;

    /**
     * Set the TTY mode.
     *
     * @param  string  $mode
     * @return void
     */
    protected function setTty($mode)
    {
        $this->initialTtyMode ??= shell_exec('stty -g');

        shell_exec("stty $mode");
    }

    /**
     * Restore the initial TTY mode.
     *
     * @return void
     */
    protected function restoreTty()
    {
        if ($this->initialTtyMode) {
            shell_exec("stty {$this->initialTtyMode}");
        }
    }
}
