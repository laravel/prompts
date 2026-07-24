<?php

namespace Laravel\Prompts;

class Tree extends Prompt
{
    /**
     * Create a new Tree instance.
     *
     * @param  array<int|string, string|array<int|string, mixed>>  $items
     */
    public function __construct(public array $items)
    {
        //
    }

    /**
     * Display the tree.
     */
    public function display(): void
    {
        $this->prompt();
    }

    /**
     * Display the tree.
     */
    public function prompt(): bool
    {
        $this->capturePreviousNewLines();

        if (static::shouldFallback()) {
            return $this->fallback();
        }

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
