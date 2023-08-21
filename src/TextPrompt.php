<?php

namespace Laravel\Prompts;

use Closure;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Style\SymfonyStyle;

class TextPrompt extends Prompt
{
    use Concerns\TypedValue;

    /**
     * Create a new TextPrompt instance.
     */
    public function __construct(
        public string $label,
        public string $placeholder = '',
        public string $default = '',
        public bool|string $required = false,
        public ?Closure $validate = null,
        public string $hint = ''
    ) {
        $this->trackTypedValue($default);
    }

    /**
     * Get the entered value with a virtual cursor.
     */
    public function valueWithCursor(int $maxWidth): string
    {
        if ($this->value() === '') {
            return $this->dim($this->addCursor($this->placeholder, 0, $maxWidth));
        }

        return $this->addCursor($this->value(), $this->cursorPosition, $maxWidth);
    }

    /**
     * Configure the default fallback behavior.
     */
    protected function configureDefaultFallback(): void
    {
        self::fallbackUsing(fn (self $prompt) => $this->retryUntilValid(
            fn () => (new SymfonyStyle(new ArrayInput([]), static::output()))->ask($prompt->label, $prompt->default ?: null) ?? '',
            $prompt->required,
            $prompt->validate,
            fn ($message) => static::output()->writeln("<error>{$message}</error>"),
        ));
    }
}
