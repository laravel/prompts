<?php

namespace Laravel\Prompts;

class PausePrompt extends Prompt
{
    /**
     * Whether the prompt has been confirmed.
     */
    public bool $confirmed = false;

    /**
     * Create a new PausePrompt instance.
     */
    public function __construct(
        public string $body,
        public string $title = '',
        public string $info = 'ENTER to continue or Ctrl+C to cancel',
        public bool|string $required = 'Please, press ENTER to continue or Ctrl+C to cancel.',
        public string $hint = '',
    ) {
        $this->validate = null;
        $this->on('key', fn ($key) => match ($key) {
            default => $this->onKey($key),
        });
    }

    /**
     * Check key pressed to allow to continue case it's enter
     */
    public function onKey(string $key): void
    {
        if ($key === Key::ENTER) {
            $this->confirmed = true;
        }
        $this->submit();
    }

    /**
     * Get the value of the prompt.
     */
    public function value(): bool
    {
        return $this->confirmed;
    }
}
