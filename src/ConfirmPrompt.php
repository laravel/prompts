<?php

namespace Laravel\Prompts;

use Closure;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Style\SymfonyStyle;

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
        public ?Closure $validate = null,
        public string $hint = ''
    ) {
        $this->confirmed = $default;

        $this->on('key', fn ($key) => match ($key) {
            'y' => $this->confirmed = true,
            'n' => $this->confirmed = false,
            Key::TAB, Key::UP, Key::UP_ARROW, Key::DOWN, Key::DOWN_ARROW, Key::LEFT, Key::LEFT_ARROW, Key::RIGHT, Key::RIGHT_ARROW, 'h', 'j', 'k', 'l' => $this->confirmed = ! $this->confirmed,
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
     * Configure the default fallback behavior.
     */
    protected function configureDefaultFallback(): void
    {
        self::fallbackUsing(fn (self $prompt) => $this->retryUntilValid(
            fn () => (new SymfonyStyle(new ArrayInput([]), static::output()))->confirm($prompt->label, $prompt->default),
            $prompt->required,
            $prompt->validate,
            fn ($message) => static::output()->writeln("<error>{$message}</error>"),
        ));
    }
}
