<?php

namespace Laravel\Prompts;

/**
 * Prompt the user for text input.
 *
 * @param  string  $message
 * @param  string|null  $placeholder
 * @param  string|null  $default
 * @param  Closure|null  $validate
 * @return string|null
 */
function text($message, $placeholder = null, $default = null, $validate = null) {
    return (new TextPrompt(
        $message,
        $placeholder,
        $default,
        $validate,
    ))->prompt();
}

/**
 * Prompt the user for input, hiding the value.
 *
 * @param  string  $message
 * @param  Closure|null  $validate
 * @return string|null
 */
function password($message, $validate = null) {
    return (new PasswordPrompt(
        $message,
        $validate,
    ))->prompt();
}

/**
 * Prompt the user to select an option.
 *
 * @param  string  $message
 * @param  array<int|string, string>  $options
 * @param  string|null  $default
 * @return string
 */
function select($message, $options, $default = null) {
    return (new SelectPrompt(
        $message,
        $options,
        $default,
    ))->prompt();
}

/**
 * Prompt the user to select multiple options.
 *
 * @param  string  $message
 * @param  array<int|string, string>  $options
 * @param  array<int, string>  $default
 * @param  Closure|null  $validate
 * @return array<int, string>
 */
function multiselect($message, $options, $default = [], $validate = null) {
    return (new MultiSelectPrompt(
        $message,
        $options,
        $default,
        $validate,
    ))->prompt();
}

/**
 * Prompt the user to confirm an action.
 *
 * @param  string  $message
 * @param  bool  $default
 * @return bool
 */
function confirm($message, $default = true) {
    return (new ConfirmPrompt(
        $message,
        $default,
    ))->prompt();
}

/**
 * Render a spinner while the given callback is executing.
 *
 * @param  \Closure  $callback
 * @param  string  $message
 * @return mixed
 */
function spin($callback, $message = '') {
    return (new Spinner($message))->spin($callback);
}

/**
 * Display a note.
 *
 * @param  string  $message
 * @param  string|null  $type
 * @return void
 */
function note($message, $type = null) {
    return (new Note($message, $type))->display();
}

/**
 * Display an error.
 *
 * @param  string  $message
 * @return void
 */
function error($message) {
    return (new Note($message, 'error'))->display();
}

/**
 * Display an introduction.
 *
 * @param  string  $message
 * @return void
 */
function intro($message) {
    return (new Note($message, 'intro'))->display();
}

/**
 * Display a closing message.
 *
 * @param  string  $message
 * @return void
 */
function outro($message) {
    return (new Note($message, 'outro'))->display();
}
