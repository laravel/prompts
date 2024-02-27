<?php

namespace Laravel\Prompts\Output;

use Symfony\Component\Console\Formatter\OutputFormatterInterface;
use Symfony\Component\Console\Output\ConsoleOutput as SymfonyConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;

class ConsoleOutput extends SymfonyConsoleOutput
{
    /**
     * How many new lines were written by the last output.
     */
    protected int $newLinesWritten = 1;

    protected OutputInterface $promptOutput;

    public function __construct(
        int $verbosity = self::VERBOSITY_NORMAL,
        ?bool $decorated = null,
        ?OutputFormatterInterface $formatter = null
    ) {
        parent::__construct($verbosity, $decorated, $formatter);

        if (stream_isatty(STDERR) && ! stream_isatty(STDOUT)) {
            $this->promptOutput = $this->getErrorOutput();
        } else {
            $this->promptOutput = new StreamOutput($this->getStream());
        }
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
    protected function doWrite(string $message, bool $newline): void
    {
        $this->promptOutput->write($message, $newline);

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
    public function writeDirectly(string $message): void
    {
        $this->promptOutput->write($message, false);
    }
}
