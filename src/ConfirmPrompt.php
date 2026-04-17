<?php

namespace Laravel\Prompts;

use Closure;

class ConfirmPrompt extends Prompt
{
    /**
     * Whether the prompt has been confirmed.
     */
    public bool $confirmed;

    /**
     * Create a new ConfirmPrompt instance.
     */
    public function __construct(
        public string $label,
        public bool $default = true,
        public string $yes = 'Yes',
        public string $no = 'No',
        public bool|string $required = false,
        public mixed $validate = null,
        public string $hint = '',
        public ?Closure $transform = null,
        mixed $skipWhen = null,
    ) {
        parent::__construct($skipWhen);

        $this->confirmed = $default;

        $this->on('key', fn ($key) => match ($key) {
            'y' => $this->confirmed = true,
            'n' => $this->confirmed = false,
            Key::TAB, Key::UP, Key::UP_ARROW, Key::DOWN, Key::DOWN_ARROW, Key::LEFT, Key::LEFT_ARROW, Key::RIGHT, Key::RIGHT_ARROW, Key::CTRL_P, Key::CTRL_F, Key::CTRL_N, Key::CTRL_B, 'h', 'j', 'k', 'l' => $this->confirmed = ! $this->confirmed,
            Key::ENTER => $this->submit(),
            default => null,
        });
    }

    /**
     * Get the value of the prompt.
     */
    public function value(): bool
    {
        return $this->confirmed;
    }

    /**
     * Get the label of the selected option.
     */
    public function label(): string
    {
        return $this->confirmed ? $this->yes : $this->no;
    }

    /**
     * Coerce a pre-supplied skip value into a boolean.
     */
    protected function coerceSkipped(mixed $value): mixed
    {
        if (is_bool($value) || $value === null) {
            return $value;
        }

        if (is_string($value)) {
            $parsed = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

            return $parsed ?? $value;
        }

        return (bool) $value;
    }
}
