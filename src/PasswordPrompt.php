<?php

namespace Laravel\Prompts;

use Closure;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Style\SymfonyStyle;

class PasswordPrompt extends Prompt
{
    use Concerns\TypedValue;

    /**
     * Create a new PasswordPrompt instance.
     */
    public function __construct(
        public string $label,
        public string $placeholder = '',
        public bool|string $required = false,
        public ?Closure $validate = null,
        public string $hint = ''
    ) {
        $this->trackTypedValue();
    }

    /**
     * Get a masked version of the entered value.
     */
    public function masked(): string
    {
        return str_repeat('•', mb_strlen($this->value()));
    }

    /**
     * Get the masked value with a virtual cursor.
     */
    public function maskedWithCursor(int $maxWidth): string
    {
        if ($this->value() === '') {
            return $this->dim($this->addCursor($this->placeholder, 0, $maxWidth));
        }

        return $this->addCursor($this->masked(), $this->cursorPosition, $maxWidth);
    }

    /**
     * Configure the default fallback behavior.
     */
    protected function configureDefaultFallback(): void
    {
        self::fallbackUsing(fn (self $prompt) => $this->retryUntilValid(
            fn () => (new SymfonyStyle(new ArrayInput([]), static::output()))->askHidden($prompt->label) ?? '',
            $prompt->required,
            $prompt->validate,
            fn ($message) => static::output()->writeln("<error>{$message}</error>"),
        ));
    }
}
