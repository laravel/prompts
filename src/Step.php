<?php

namespace Laravel\Prompts;

class Step extends Prompt
{
    public function __construct(
        public string $title,
        public int $currentStep,
        public int $totalSteps,
    )
    {
    }

    /**
     * Display the step.
     */
    public function display(): void
    {
        $this->prompt();
    }

    /**
     * Display the step.
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
