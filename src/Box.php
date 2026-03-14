<?php

namespace Laravel\Prompts;

class Box extends Prompt
{
    /**
     * Create a new Box instance.
     */
    public function __construct(
        public string $message,
        public string $title = '',
        public string $footer = '',
        public string $color = 'gray',
        public string $info = '',
    ) {
        //
    }

    /**
     * Display the box.
     */
    public function display(): void
    {
        $this->prompt();
    }

    /**
     * Display the box.
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
