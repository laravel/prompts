<?php

namespace Laravel\Prompts\Output;

use Symfony\Component\Console\Output\ConsoleOutput;

class BufferedConsoleOutput extends ConsoleOutput
{
    /**
     * The output buffer.
     */
    protected string $buffer = '';

    /**
     * Empties the buffer and returns its content.
     */
    public function fetch(): string
    {
        $content = $this->buffer;
        $this->buffer = '';

        return $content;
    }

    /**
     * Write to the output buffer.
     */
    protected function doWrite(string $message, bool $newline): void
    {
        $this->buffer .= $message;

        if ($newline) {
            $this->buffer .= \PHP_EOL;
        }
    }
}
