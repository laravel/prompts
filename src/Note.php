<?php

namespace Laravel\Prompts;

class Note extends Prompt
{
    /**
     * Create a new Note instance.
     *
     * @param  string  $message
     * @param  string|null  $type
     * @return void
     */
    public function __construct(public $message, public $type = null)
    {
        //
    }

    /**
     * Display the note.
     *
     * @return void
     */
    public function display()
    {
        fwrite(STDOUT, $this->renderTheme());
    }

    /**
     * Display the note.
     *
     * @return void
     */
    public function prompt()
    {
        return $this->display();
    }

    /**
     * Get the value of the prompt.
     *
     * @return void
     */
    public function value()
    {
        return null;
    }
}
