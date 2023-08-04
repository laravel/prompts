<?php

namespace Laravel\Prompts\Themes\Default;

use Laravel\Prompts\Concerns\Colors;
use Laravel\Prompts\Concerns\Truncation;
use Laravel\Prompts\Prompt;
use RuntimeException;

abstract class Renderer
{
    use Truncation;
    use Colors;

    /**
     * The output to be rendered.
     */
    protected string $output = '';

    /**
     * Create a new renderer instance.
     */
    public function __construct(protected Prompt $prompt)
    {
        $this->checkTerminalSize($prompt);
    }

    /**
     * Render a line of output.
     */
    protected function line(string $message): self
    {
        $this->output .= $message.PHP_EOL;

        return $this;
    }

    /**
     * Render a new line.
     */
    protected function newLine(int $count = 1): self
    {
        $this->output .= str_repeat(PHP_EOL, $count);

        return $this;
    }

    /**
     * Render a warning message.
     */
    protected function warning(string $message): self
    {
        return $this->line($this->yellow("  ⚠ {$message}"));
    }

    /**
     * Render an error message.
     */
    protected function error(string $message): self
    {
        return $this->line($this->red("  ⚠ {$message}"));
    }

    /**
     * Render the output with a blank line above and below.
     */
    public function __toString()
    {
        return str_repeat(PHP_EOL, 2 - $this->prompt->newLinesWritten())
            .$this->output
            .(in_array($this->prompt->state, ['submit', 'cancel']) ? PHP_EOL : '');
    }

    /**
     * Check that the terminal is large enough to render the prompt.
     */
    private function checkTerminalSize(Prompt $prompt): void
    {
        $required = 8;
        $actual = $prompt->terminal()->lines();

        if ($actual < $required) {
            throw new RuntimeException(
                "The terminal height must be at least [$required] lines but is currently [$actual]. Please increase the height or reduce the font size."
            );
        }
    }
}
