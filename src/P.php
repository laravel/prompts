<?php

namespace Laravel\Prompts;

use Closure;
use Illuminate\Support\Collection;

class P
{
    public static function text(
        string $label,
        string $placeholder = '',
        string $default = '',
        bool|string $required = false,
        Closure $validate = null
    ): string {
        return (new TextPrompt($label, $placeholder, $default, $required, $validate))->prompt();
    }

    /**
     * Prompt the user for input, hiding the value.
     */
    public static function password(
        string $label,
        string $placeholder = '',
        bool|string $required = false,
        Closure $validate = null
    ): string {
        return (new PasswordPrompt($label, $placeholder, $required, $validate))->prompt();
    }

    /**
     * Prompt the user to select an option.
     *
     * @param  array<int|string, string>|Collection<int|string, string>  $options
     */
    public static function select(
        string $label,
        array|Collection $options,
        int|string $default = null,
        int $scroll = 5,
        Closure $validate = null
    ): int|string {
        return (new SelectPrompt($label, $options, $default, $scroll, $validate))->prompt();
    }

    /**
     * Prompt the user to select multiple options.
     *
     * @param  array<int|string, string>|Collection<int|string, string>  $options
     * @param  array<int|string>|Collection<int, int|string>  $default
     * @return array<int|string>
     */
    public static function multiselect(
        string $label,
        array|Collection $options,
        array|Collection $default = [],
        int $scroll = 5,
        bool|string $required = false,
        Closure $validate = null
    ): array {
        return (new MultiSelectPrompt($label, $options, $default, $scroll, $required, $validate))->prompt();
    }

    /**
     * Prompt the user to confirm an action.
     */
    public static function confirm(
        string $label,
        bool $default = true,
        string $yes = 'Yes',
        string $no = 'No',
        bool|string $required = false,
        Closure $validate = null
    ): bool {
        return (new ConfirmPrompt($label, $default, $yes, $no, $required, $validate))->prompt();
    }

    /**
     * Prompt the user for text input with auto-completion.
     *
     * @param  array<string>|Collection<int, string>|Closure(string): array<string>  $options
     */
    public static function suggest(
        string $label,
        array|Collection|Closure $options,
        string $placeholder = '',
        string $default = '',
        int $scroll = 5,
        bool|string $required = false,
        Closure $validate = null
    ): string {
        return (new SuggestPrompt($label, $options, $placeholder, $default, $scroll, $required, $validate))->prompt();
    }

    /**
     * Allow the user to search for an option.
     *
     * @param  Closure(string): array<int|string, string>  $options
     */
    public static function search(
        string $label,
        Closure $options,
        string $placeholder = '',
        int $scroll = 5,
        Closure $validate = null
    ): int|string {
        return (new SearchPrompt($label, $options, $placeholder, $scroll, $validate))->prompt();
    }

    /**
     * Render a spinner while the given callback is executing.
     *
     * @template TReturn of mixed
     *
     * @param  \Closure(): TReturn  $callback
     * @return TReturn
     */
    public static function spin(Closure $callback, string $message = ''): mixed
    {
        return (new Spinner($message))->spin($callback);
    }

    /**
     * Display a note.
     */
    public static function note(string $message, string $type = null): void
    {
        (new Note($message, $type))->display();
    }

    /**
     * Display an error.
     */
    public static function error(string $message): void
    {
        (new Note($message, 'error'))->display();
    }

    /**
     * Display a warning.
     */
    public static function warning(string $message): void
    {
        (new Note($message, 'warning'))->display();
    }

    /**
     * Display an alert.
     */
    public static function alert(string $message): void
    {
        (new Note($message, 'alert'))->display();
    }

    /**
     * Display an introduction.
     */
    public static function intro(string $message): void
    {
        (new Note($message, 'intro'))->display();
    }

    /**
     * Display a closing message.
     */
    public static function outro(string $message): void
    {
        (new Note($message, 'outro'))->display();
    }
}
