<?php

namespace Laravel\Prompts;

class Note extends Prompt
{
    /**
     * Create a new Note instance.
     */
    public function __construct(public string $message, public ?string $type = null)
    {
        //
    }

    /**
     * Display the note.
     */
    public function display(): void
    {
        $this->terminal()->write($this->renderTheme());
    }

    /**
     * Display the note.
     */
    public function prompt(): null
    {
        $this->display();

        return null;
    }

    /**
     * Get the value of the prompt.
     */
    public function value(): null
    {
        return null;
    }
}
