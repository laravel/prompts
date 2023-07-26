<?php

namespace Laravel\Prompts;

class Terminal
{
    /**
     * The initial TTY mode.
     */
    protected ?string $initialTtyMode;

    /**
     * The number of columns in the terminal.
     */
    protected int $cols;

    /**
     * The number of lines in the terminal.
     */
    protected int $lines;

    /**
     * Read a line from the terminal.
     */
    public function read(): string
    {
        return fread(STDIN, 1024) ?: '';
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
        $this->initialTtyMode ??= (shell_exec('stty -g') ?: null);

        shell_exec("stty $mode");
    }

    /**
     * Restore the initial TTY mode.
     */
    public function restoreTty(): void
    {
        if ($this->initialTtyMode) {
            shell_exec("stty {$this->initialTtyMode}");

            $this->initialTtyMode = null;
        }
    }

    /**
     * Get the number of columns in the terminal.
     */
    public function cols(): int
    {
        return $this->cols ??= (int) shell_exec('tput cols 2>/dev/null');
    }

    /**
     * Get the number of lines in the terminal.
     */
    public function lines(): int
    {
        return $this->lines ??= (int) shell_exec('tput lines 2>/dev/null');
    }

    /**
     * Exit the interactive session.
     */
    public function exit(): void
    {
        exit(1);
    }
}
