<?php

namespace Laravel\Prompts;

use Closure;

/**
 * Prompt the user for text input.
 */
function text(string $label, string $placeholder = '', string $default = '', ?Closure $validate = null): string
{
    return (new TextPrompt($label, $placeholder, $default, $validate))->prompt();
}

/**
 * Prompt the user for input, hiding the value.
 */
function password(string $label, ?Closure $validate = null): string
{
    return (new PasswordPrompt($label, $validate))->prompt();
}

/**
 * Prompt the user to select an option.
 *
 * @param  array<int|string, string>  $options
 */
function select(string $label, array $options, ?string $default = null, int $scroll = 5): string
{
    return (new SelectPrompt($label, $options, $default, $scroll))->prompt();
}

/**
 * Prompt the user to select multiple options.
 *
 * @param  array<int|string, string>  $options
 * @param  array<string>  $default
 * @return array<string>
 */
function multiselect(string $label, array $options, array $default = [], int $scroll = 5, ?Closure $validate = null): array
{
    return (new MultiSelectPrompt($label, $options, $default, $scroll, $validate))->prompt();
}

/**
 * Prompt the user to confirm an action.
 */
function confirm(string $label, bool $default = true, string $yes = 'Yes', string $no = 'No'): bool
{
    return (new ConfirmPrompt(
        $label,
        $default,
        $yes,
        $no,
    ))->prompt();
}

/**
 * Prompt the user for text input with auto-completion.
 *
 * @param  array<string>|Closure(string): array<string>  $options
 */
function suggest(string $label, array|Closure $options, string $placeholder = '', string $default = '', int $scroll = 5, ?Closure $validate = null): string
{
    return (new SuggestPrompt($label, $options, $placeholder, $default, $scroll, $validate))->prompt();
}

/**
 * Render a spinner while the given callback is executing.
 *
 * @template TReturn of mixed
 *
 * @param  \Closure(): TReturn  $callback
 * @return TReturn
 */
function spin(Closure $callback, string $message = ''): mixed
{
    return (new Spinner($message))->spin($callback);
}

/**
 * Display a note.
 */
function note(string $message, ?string $type = null): void
{
    (new Note($message, $type))->display();
}

/**
 * Display an error.
 */
function error(string $message): void
{
    (new Note($message, 'error'))->display();
}

/**
 * Display a warning.
 */
function warning(string $message): void
{
    (new Note($message, 'warning'))->display();
}

/**
 * Display an alert.
 */
function alert(string $message): void
{
    (new Note($message, 'alert'))->display();
}

/**
 * Display an introduction.
 */
function intro(string $message): void
{
    (new Note($message, 'intro'))->display();
}

/**
 * Display a closing message.
 */
function outro(string $message): void
{
    (new Note($message, 'outro'))->display();
}
