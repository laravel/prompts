<?php

namespace Laravel\Prompts\Concerns;

use Closure;
use RuntimeException;

trait Fallback
{
    /**
     * Whether to fallback to a custom implementation
     */
    protected static bool $shouldFallback = PHP_OS_FAMILY === 'Windows';

    /**
     * The fallback implementations.
     *
     * @var array<class-string, Closure($this): mixed>
     */
    protected static array $fallbacks = [];

    /**
     * Enable the fallback implementation.
     */
    public static function fallbackWhen(bool $condition): void
    {
        static::$shouldFallback = $condition || static::$shouldFallback;
    }

    /**
     * Whether the prompt should fallback to a custom implementation.
     */
    public static function shouldFallback(): bool
    {
        return static::$shouldFallback && static::hasFallback();
    }

    /**
     * Whether the prompt has a fallback implementation.
     */
    public static function hasFallback(): bool
    {
        return isset(static::$fallbacks[static::class]);
    }

    /**
     * Set the fallback implementation.
     *
     * @param  Closure($this): mixed  $fallback
     */
    public static function fallbackUsing(Closure $fallback): void
    {
        static::$fallbacks[static::class] = $fallback;
    }

    /**
     * Call the registered fallback implementation.
     */
    public function fallback(): mixed
    {
        $fallback = static::$fallbacks[static::class] ?? null;

        if ($fallback === null) {
            throw new RuntimeException('No fallback implementation registered for ['.static::class.']');
        }

        return $fallback($this);
    }

    /**
     * Configure the default fallback behavior.
     */
    abstract protected function configureDefaultFallback(): void;

    /**
     * Retry the callback until the validation passes.
     *
     * @param  Closure(): mixed  $callback
     * @param  Closure(mixed): string|null  $validate
     * @param  Closure(string): void  $fail
     */
    protected function retryUntilValid(Closure $callback, bool|string $required, ?Closure $validate, Closure $fail): mixed
    {
        while (true) {
            $result = $callback();

            if ($required && ($result === '' || $result === [] || $result === false)) {
                $fail(is_string($required) ? $required : 'Required.');

                continue;
            }

            if ($validate) {
                $error = $validate($result);

                if (is_string($error) && strlen($error) > 0) {
                    $fail($error);

                    continue;
                }
            }

            return $result;
        }
    }
}
