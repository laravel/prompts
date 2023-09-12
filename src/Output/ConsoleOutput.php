<?php

namespace Laravel\Prompts\Output;

use Laravel\Prompts\Prompt;
use Symfony\Component\Console\Output\ConsoleOutput as SymfonyConsoleOutput;

class ConsoleOutput extends SymfonyConsoleOutput
{
    public static $debug = false;

    /**
     * How many new lines were written by the last output.
     */
    protected int $newLinesWritten = 1;

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
        if ($newline) {
            $message .= \PHP_EOL;
        }

        $trailingNewLines = strlen($message) - strlen(rtrim($message, \PHP_EOL));

        if (trim($message) === '') {
            $this->newLinesWritten += $trailingNewLines;
        } else {
            $this->newLinesWritten = $trailingNewLines;
        }

        parent::doWrite(static::$debug ? $this->addDebugMarkers($message) : $message, false);
    }

    /**
     * Write output directly, bypassing newline capture.
     */
    public function writeDirectly(string $message): void
    {
        parent::doWrite($message, false);
    }

    /**
     * Add debug markers to the given output.
     */
    protected function addDebugMarkers(string $output): string
    {
        // Add the current render count to the end of each line.
        $output = preg_replace('/$/m', Prompt::$renderCount, $output);

        // Add markers to invisible characters.
        return str_replace([PHP_EOL, ' '], ['↵'.PHP_EOL, '␣'], $output);
    }
}
