<?php

namespace Laravel\Prompts;

use Illuminate\Support\Collection;

class Table extends Prompt
{
    /**
     * Create a new Table instance.
     *
     * @param  array<int, string>|Collection<int, string>  $headers
     * @param  array<int, string>|Collection<int, string>  $rows
     */
    public function __construct(public array|Collection $headers, public array|Collection $rows)
    {
        $this->headers = $this->headers instanceof Collection ? $this->headers->all() : $this->headers;
        $this->rows = $this->rows instanceof Collection ? $this->rows->all() : $this->rows;
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
