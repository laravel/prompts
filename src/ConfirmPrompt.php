<?php

namespace Laravel\Prompts;

class ConfirmPrompt extends Prompt
{
    /**
     * Whether the prompt has been confirmed.
     *
     * @var bool
     */
    public $confirmed;

    /**
     * Create a new ConfirmPrompt instance.
     *
     * @param  string  $message
     * @param  bool  $default
     * @return void
     */
    public function __construct(
        public $message,
        $default = true
    ) {
        $this->confirmed = $default;

        $this->on('key', fn ($key) => match ($key) {
            'y' => $this->confirmed = true,
            'n' => $this->confirmed = false,
            Key::UP, Key::DOWN, Key::LEFT, Key::RIGHT, 'h', 'j', 'k', 'l' => $this->confirmed = ! $this->confirmed,
            default => null,
        });
    }

    /**
     * Get the value of the prompt.
     *
     * @return bool
     */
    public function value()
    {
        return $this->confirmed;
    }
}
