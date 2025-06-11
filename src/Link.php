<?php

namespace Laravel\Prompts;

class Link extends Prompt
{
    /**
     * Create a new link prompt instance.
     */
    public function __construct(public string $message, public string $path, public ?string $tooltip = '')
    {
        //
    }

    /**
     * Display the note.
     */
    public function display(): void
    {
        $this->prompt();
    }

    /**
     * Display the note.
     */
    public function prompt(): bool
    {
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
