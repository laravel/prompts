<?php

namespace Laravel\Prompts;

class Clear extends Prompt
{
    /**
     * Display the note.
     */
    public function prompt(): bool
    {
        $this->capturePreviousNewLines();

        static::output()->write($this->renderTheme());

        return true;
    }

    /**
     * Display the note.
     */
    public function display(): void
    {
        $this->prompt();
    }

    /**
     * Get the value of the prompt.
     */
    public function value(): mixed
    {
        return "\033[H\033[J";
    }
}
