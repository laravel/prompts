<?php

namespace Laravel\Prompts;

/**
 * Artisan Console Compatibility.
 *
 * This trait updates the Artisan console prompt methods to use Laravel Prompts.
 * Some features provided by Laravel Prompts are not available with the Artisan Console methods.
 */
trait Prompts
{
    /**
     * Prompt the user for input.
     *
     * @param  string  $question
     * @param  string|null  $default
     * @return mixed
     */
    public function ask($question, $default = null)
    {
        return text(
            message: $question,
            default: $default ?? '',
        );
    }

    /**
     * Prompt the user for input but hide the answer from the console.
     *
     * @param  string  $question
     * @param  bool  $fallback
     * @return mixed
     */
    public function secret($question, $fallback = true)
    {
        return password(
            message: $question,
        );
    }

    /**
     * Confirm a question with the user.
     *
     * @param  string  $question
     * @param  bool  $default
     * @return bool
     */
    public function confirm($question, $default = false)
    {
        return confirm(
            message: $question,
            default: $default
        );
    }

    /**
     * Give the user a single choice from an array of answers.
     *
     * @param  string  $question
     * @param  string|int|null  $default
     * @param  mixed|null  $attempts
     * @param  bool  $multiple
     * @return string|array
     */
    public function choice($question, array $choices, $default = null, $attempts = null, $multiple = false)
    {
        if (! $multiple) {
            return select(
                message: $question,
                default: $default,
                options: $choices,
            );
        }

        return multiselect(
            message: $question,
            options: $choices,
            default: explode(',', $default),
        );
    }

    /**
     * Prompt the user for input with auto completion.
     *
     * @param  string  $question
     * @param  array|callable  $choices
     * @param  string|null  $default
     * @return mixed
     */
    public function anticipate($question, $choices, $default = null)
    {
        return $this->askWithCompletion($question, $choices, $default ?? '');
    }

    /**
     * Prompt the user for input with auto completion.
     *
     * @param  string  $question
     * @param  array|callable  $choices
     * @param  string|null  $default
     * @return mixed
     */
    public function askWithCompletion($question, $choices, $default = null)
    {
        return anticipate(
            message: $question,
            options: $choices,
            default: $default,
        );
    }

    /**
     * Write a string as information output.
     *
     * @param  string  $string
     * @param  int|string|null  $verbosity
     * @return void
     */
    public function info($string, $verbosity = null)
    {
        return note($string);
    }

    /**
     * Write a string as error output.
     *
     * @param  string  $string
     * @param  int|string|null  $verbosity
     * @return void
     */
    public function error($string, $verbosity = null)
    {
        return error($string);
    }

    /**
     * Write a string as warning output.
     *
     * @param  string  $string
     * @param  int|string|null  $verbosity
     * @return void
     */
    public function warn($string, $verbosity = null)
    {
        return warning($string);
    }

    /**
     * Write a string in an alert box.
     *
     * @param  string  $string
     * @param  int|string|null  $verbosity
     * @return void
     */
    public function alert($string, $verbosity = null)
    {
        return alert($string);
    }

    /**
     * Write a string as comment output.
     *
     * @param  string  $string
     * @param  int|string|null  $verbosity
     * @return void
     */
    public function comment($string, $verbosity = null)
    {
        return note($string);
    }

    /**
     * Write a string as question output.
     *
     * @param  string  $string
     * @param  int|string|null  $verbosity
     * @return void
     */
    public function question($string, $verbosity = null)
    {
        return note($string);
    }

    /**
     * Write an introductory string.
     *
     * @param  string  $string
     * @param  int|string|null  $verbosity
     * @return void
     */
    public function intro($message, $verbosity = null)
    {
        return intro($message);
    }

    /**
     * Write an outro string.
     *
     * @param  string  $string
     * @param  int|string|null  $verbosity
     * @return void
     */
    public function outro($message, $verbosity = null)
    {
        return outro($message);
    }

    /**
     * Render a spinner while the given callback runs.
     *
     * @param  \Closure  $callback
     * @param  string  $message
     * @return void
     */
    public function spin($callback, $message = '')
    {
        return spin($callback, $message);
    }
}
