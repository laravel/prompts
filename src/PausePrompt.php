<?php

namespace Laravel\Prompts;

class PausePrompt extends Prompt
{
    /**
     * Whether enter key has been pressed.
     */
    public bool $enterPressed = false;

    /**
     * Create a new PausePrompt instance.
     */
    public function __construct(
        public string $message = 'Press enter to continue...',
    ) {
        $this->required = $this->message;
        $this->validate = null;
        $this->on('key', fn ($key) => $this->onKey($key));
    }

    /**
     * Check key pressed to allow to continue case it's enter
     */
    public function onKey(string $key): void
    {
        if ($key === Key::ENTER) {
            $this->enterPressed = true;
        }
        $this->submit();
    }

    /**
     * Get the value of the prompt.
     */
    public function value(): bool
    {
        return $this->enterPressed;
    }
}
