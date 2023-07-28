<?php

namespace Laravel\Prompts;

use Closure;
use Illuminate\Support\Collection;

/**
 * Prompt the user for text input.
 */
function text(
    string $label,
    string $placeholder = '',
    string $default = '',
    bool|string $required = false,
    Closure $validate = null
): string {
    return P::text($label, $placeholder, $default, $required, $validate);
}

/**
 * Prompt the user for input, hiding the value.
 */
function password(
    string $label,
    string $placeholder = '',
    bool|string $required = false,
    Closure $validate = null
): string {
    return P::password($label, $placeholder, $required, $validate);
}

/**
 * Prompt the user to select an option.
 *
 * @param  array<int|string, string>|Collection<int|string, string>  $options
 */
function select(
    string $label,
    array|Collection $options,
    int|string $default = null,
    int $scroll = 5,
    Closure $validate = null
): int|string {
    return P::select($label, $options, $default, $scroll, $validate);
}

/**
 * Prompt the user to select multiple options.
 *
 * @param  array<int|string, string>|Collection<int|string, string>  $options
 * @param  array<int|string>|Collection<int, int|string>  $default
 * @return array<int|string>
 */
function multiselect(
    string $label,
    array|Collection $options,
    array|Collection $default = [],
    int $scroll = 5,
    bool|string $required = false,
    Closure $validate = null
): array {
    return P::multiselect($label, $options, $default, $scroll, $required, $validate);
}

/**
 * Prompt the user to confirm an action.
 */
function confirm(
    string $label,
    bool $default = true,
    string $yes = 'Yes',
    string $no = 'No',
    bool|string $required = false,
    Closure $validate = null
): bool {
    return P::confirm($label, $default, $yes, $no, $required, $validate);
}

/**
 * Prompt the user for text input with auto-completion.
 *
 * @param  array<string>|Collection<int, string>|Closure(string): array<string>  $options
 */
function suggest(
    string $label,
    array|Collection|Closure $options,
    string $placeholder = '',
    string $default = '',
    int $scroll = 5,
    bool|string $required = false,
    Closure $validate = null
): string {
    return P::suggest($label, $options, $placeholder, $default, $scroll, $required, $validate);
}

/**
 * Allow the user to search for an option.
 *
 * @param  Closure(string): array<int|string, string>  $options
 */
function search(
    string $label,
    Closure $options,
    string $placeholder = '',
    int $scroll = 5,
    Closure $validate = null
): int|string {
    return P::search($label, $options, $placeholder, $scroll, $validate);
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
    return P::spin($callback, $message);
}

/**
 * Display a note.
 */
function note(string $message, string $type = null): void
{
    P::note($message, $type);
}

/**
 * Display an error.
 */
function error(string $message): void
{
    P::error($message);
}

/**
 * Display a warning.
 */
function warning(string $message): void
{
    P::warning($message);
}

/**
 * Display an alert.
 */
function alert(string $message): void
{
    P::alert($message);
}

/**
 * Display an introduction.
 */
function intro(string $message): void
{
    P::intro($message);
}

/**
 * Display a closing message.
 */
function outro(string $message): void
{
    P::outro($message);
}
