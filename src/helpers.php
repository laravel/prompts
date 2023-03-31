<?php

namespace Laravel\Prompts;

use Closure;

/**
 * Prompt the user for text input.
 */
function text(string $message, string $placeholder = '', string $default = '', ?Closure $validate = null): string
{
    return (new TextPrompt($message, $placeholder, $default, $validate))->prompt();
}

/**
 * Prompt the user for input, hiding the value.
 */
function password(string $message, ?Closure $validate = null): string
{
    return (new PasswordPrompt($message, $validate))->prompt();
}

/**
 * Prompt the user to select an option.
 *
 * @param  array<int|string, string>  $options
 */
function select(string $message, array $options, ?string $default = null): string
{
    return (new SelectPrompt($message, $options, $default))->prompt();
}

/**
 * Prompt the user to select multiple options.
 *
 * @param  array<int|string, string>  $options
 * @param  array<string>  $default
 * @return array<string>
 */
function multiselect(string $message, array $options, array $default = [], ?Closure $validate = null): array
{
    return (new MultiSelectPrompt($message, $options, $default, $validate))->prompt();
}

/**
 * Prompt the user to confirm an action.
 */
function confirm(string $message, bool $default = true): bool
{
    return (new ConfirmPrompt(
        $message,
        $default,
    ))->prompt();
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
