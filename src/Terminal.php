<?php

namespace Laravel\Prompts;

class Terminal
{
    /**
     * The initial TTY mode.
     */
    protected string $initialTtyMode;

    /**
     * Read a line from the terminal.
     */
    public function read(): string
    {
        return fread(STDIN, 1024);
    }

    /**
     * Write data to the terminal.
     */
    public function write(string $data): void
    {
        fwrite(STDOUT, $data);
    }

    /**
     * Set the TTY mode.
     */
    public function setTty(string $mode): void
    {
        $this->initialTtyMode ??= shell_exec('stty -g');

        shell_exec("stty $mode");
    }

    /**
     * Restore the initial TTY mode.
     */
    public function restoreTty(): void
    {
        if ($this->initialTtyMode) {
            shell_exec("stty {$this->initialTtyMode}");
        }
    }

    /**
     * Exit the interactive session.
     */
    public function exit(): void
    {
        exit(1);
    }
}
