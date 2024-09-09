<?php

namespace Laravel\Prompts\Support\Facades;

use BadMethodCallException;
use Laravel\Prompts\FormBuilder;
use Laravel\Prompts\Progress;

/**
 * @method static string text(string $label, string $placeholder = '', string $default = '', bool|string $required = false, mixed $validate = null, string $hint = '', ?Closure $transform = null)
 * @method static string textarea(string $label, string $placeholder = '', string $default = '', bool|string $required = false, mixed $validate = null, string $hint = '', int $rows = 5, ?Closure $transform = null)
 * @method static string password(string $label, string $placeholder = '', bool|string $required = false, mixed $validate = null, string $hint = '', ?Closure $transform = null)
 * @method static int|string select(string $label, array|Collection $options, int|string|null $default = null, int $scroll = 5, mixed $validate = null, string $hint = '', bool|string $required = true, ?Closure $transform = null)
 * @method static array multiselect(string $label, array|Collection $options, array|Collection $default = [], int $scroll = 5, bool|string $required = false, mixed $validate = null, string $hint = 'Use the space bar to select options.', ?Closure $transform = null)
 * @method static bool confirm(string $label, bool $default = true, string $yes = 'Yes', string $no = 'No', bool|string $required = false, mixed $validate = null, string $hint = '', ?Closure $transform = null)
 * @method static bool pause(string $message = 'Press enter to continue...')
 * @method static void clear()
 * @method static string suggest(string $label, array|Collection|Closure $options, string $placeholder = '', string $default = '', int $scroll = 5, bool|string $required = false, mixed $validate = null, string $hint = '', ?Closure $transform = null)
 * @method static int|string search(string $label, Closure $options, string $placeholder = '', int $scroll = 5, mixed $validate = null, string $hint = '', bool|string $required = true, ?Closure $transform = null)
 * @method static array multisearch(string $label, Closure $options, string $placeholder = '', int $scroll = 5, bool|string $required = false, mixed $validate = null, string $hint = 'Use the space bar to select options.', ?Closure $transform = null)
 * @method static mixed spin(Closure $callback, string $message = '')
 * @method static void note(string $message, ?string $type = null)
 * @method static void error(string $message)
 * @method static void warning(string $message)
 * @method static void info(string $message)
 * @method static void alert(string $message)
 * @method static void intro(string $message)
 * @method static void outro(string $message)
 * @method static void table(array|Collection $headers = [], array|Collection|null $rows = null)
 * @method static array|Progress progress(string $label, iterable|int $steps, ?Closure $callback = null, string $hint = '')
 * @method static FormBuilder form()
 */
class Prompt
{
    /**
     * Calls the clss' function statically.
     *
     * @param  string  $name
     * @param  array<int, mixed>  $arguments
     *
     * @return mixed
     */
    public static function __callStatic(string $name, array $arguments): mixed
    {
        return static::callHelperFunction($name, $arguments);
    }


    /**
     * Calls the class' function.
     *
     * @param  string  $name
     * @param  array<int, mixed>  $arguments
     *
     * @return mixed
     */
    public function __call(string $name, array $arguments): mixed
    {
        return $this::callHelperFunction($name, $arguments);
    }

    /**
     * Returns the qualified name of the function if it exists.
     *
     * @param  string  $name
     *
     * @return string
     * @throws BadMethodCallException
     */
    protected static function resolveFunction(string $name): string
    {
        $function = '\\Laravel\\Prompts\\' . $name;

        if (!function_exists($function) || !is_callable($function)) {
            throw new BadMethodCallException(
                sprintf(
                    'Call to undefined method %s::%s()',
                    static::class,
                    $name
                )
            );
        }

        return $function;
    }

    /**
     * Calls the helper function.
     *
     * @param  string  $name
     * @param  array<int, mixed>  $arguments
     *
     * @return mixed
     */
    protected static function callHelperFunction(string $name, array $arguments): mixed
    {
        $function = static::resolveFunction($name);

        return $function(...$arguments);
    }
}
