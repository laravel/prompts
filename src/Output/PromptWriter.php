<?php

namespace Laravel\Prompts\Output;

use Symfony\Component\Console\Output\OutputInterface;

class PromptWriter
{
    /**
     * How many new lines were written by the last output.
     */
    protected int $newLinesWritten = 1;

    public function __construct(
        private OutputInterface $output
    ) {
    }

    /**
     * How many new lines were written by the last output.
     */
    public function newLinesWritten(): int
    {
        return $this->newLinesWritten;
    }

    /**
     * Write the output and capture the number of trailing new lines.
     */
    public function write(string $message, bool $newline = false): void
    {
        $this->output->write($message, $newline);

        if ($newline) {
            $message .= \PHP_EOL;
        }

        $trailingNewLines = \strlen($message) - \strlen(rtrim($message, \PHP_EOL));

        if (trim($message) === '') {
            $this->newLinesWritten += $trailingNewLines;
        } else {
            $this->newLinesWritten = $trailingNewLines;
        }
    }

    /**
     * Write output directly, bypassing newline capture.
     */
    public function writeDirectly(string $message, bool $newline = false): void
    {
        $this->output->write($message, $newline);
    }

    public function getOutput(): OutputInterface
    {
        return $this->output;
    }
}
