<?php

namespace Laravel\Prompts;

class Table extends Prompt
{
    /**
     * Create a new Table instance.
     *
     * @param  array<int, string>  $headers
     * @param  array<int, string>  $rows
     */
    public function __construct(public array $headers, public array $rows)
    {
    }

    /**
     * Display the table.
     */
    public function display(): void
    {
        $this->prompt();
    }

    /**
     * Display the table.
     */
    public function prompt(): bool
    {
        $this->capturePreviousNewLines();

        $this->state = 'submit';

        static::output()->write($this->renderTheme());

        return true;
    }

    /**
     * Get the value of the prompt.
     */
    public function value(): bool
    {
        return true;
    }
}
