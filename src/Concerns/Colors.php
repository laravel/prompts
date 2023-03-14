<?php

namespace Laravel\Prompts\Concerns;

trait Colors
{
    /**
     * Reset all colors and styles.
     *
     * @param  string  $text
     * @return string
     */
    public function reset($text)
    {
        return "\x1B[0m{$text}\x1B[0m";
    }

    /**
     * Make the text bold.
     *
     * @param  string  $text
     * @return string
     */
    public function bold($text)
    {
        return "\x1B[1m{$text}\x1B[22m";
    }

    /**
     * Make the text dim.
     *
     * @param  string  $text
     * @return string
     */
    public function dim($text)
    {
        return "\x1B[2m{$text}\x1B[22m";
    }

    /**
     * Make the text italic.
     *
     * @param  string  $text
     * @return string
     */
    public function italic($text)
    {
        return "\x1B[3m{$text}\x1B[23m";
    }

    /**
     * Underline the text.
     *
     * @param  string  $text
     * @return string
     */
    public function underline($text)
    {
        return "\x1B[4m{$text}\x1B[24m";
    }

    /**
     * Invert the text and background colors.
     *
     * @param  string  $text
     * @return string
     */
    public function inverse($text)
    {
        return "\x1B[7m{$text}\x1B[27m";
    }

    /**
     * Hide the text.
     *
     * @param  string  $text
     * @return string
     */
    public function hidden($text)
    {
        return "\x1B[8m{$text}\x1B[28m";
    }

    /**
     * Strike through the text.
     *
     * @param  string  $text
     * @return string
     */
    public function strikethrough($text)
    {
        return "\x1B[9m{$text}\x1B[29m";
    }

    /**
     * Set the text color to black.
     *
     * @param  string  $text
     * @return string
     */
    public function black($text)
    {
        return "\x1B[30m{$text}\x1B[39m";
    }

    /**
     * Set the text color to red.
     *
     * @param  string  $text
     * @return string
     */
    public function red($text)
    {
        return "\x1B[31m{$text}\x1B[39m";
    }

    /**
     * Set the text color to green.
     *
     * @param  string  $text
     * @return string
     */
    public function green($text)
    {
        return "\x1B[32m{$text}\x1B[39m";
    }

    /**
     * Set the text color to yellow.
     *
     * @param  string  $text
     * @return string
     */
    public function yellow($text)
    {
        return "\x1B[33m{$text}\x1B[39m";
    }

    /**
     * Set the text color to blue.
     *
     * @param  string  $text
     * @return string
     */
    public function blue($text)
    {
        return "\x1B[34m{$text}\x1B[39m";
    }

    /**
     * Set the text color to magenta.
     *
     * @param  string  $text
     * @return string
     */
    public function magenta($text)
    {
        return "\x1B[35m{$text}\x1B[39m";
    }

    /**
     * Set the text color to cyan.
     *
     * @param  string  $text
     * @return string
     */
    public function cyan($text)
    {
        return "\x1B[36m{$text}\x1B[39m";
    }

    /**
     * Set the text color to white.
     *
     * @param  string  $text
     * @return string
     */
    public function white($text)
    {
        return "\x1B[37m{$text}\x1B[39m";
    }

    /**
     * Set the text background to black.
     *
     * @param  string  $text
     * @return string
     */
    public function bgBlack($text)
    {
        return "\x1B[40m{$text}\x1B[49m";
    }

    /**
     * Set the text background to red.
     *
     * @param  string  $text
     * @return string
     */
    public function bgRed($text)
    {
        return "\x1B[41m{$text}\x1B[49m";
    }

    /**
     * Set the text background to green.
     *
     * @param  string  $text
     * @return string
     */
    public function bgGreen($text)
    {
        return "\x1B[42m{$text}\x1B[49m";
    }

    /**
     * Set the text background to yellow.
     *
     * @param  string  $text
     * @return string
     */
    public function bgYellow($text)
    {
        return "\x1B[43m{$text}\x1B[49m";
    }

    /**
     * Set the text background to blue.
     *
     * @param  string  $text
     * @return string
     */
    public function bgBlue($text)
    {
        return "\x1B[44m{$text}\x1B[49m";
    }

    /**
     * Set the text background to magenta.
     *
     * @param  string  $text
     * @return string
     */
    public function bgMagenta($text)
    {
        return "\x1B[45m{$text}\x1B[49m";
    }

    /**
     * Set the text background to cyan.
     *
     * @param  string  $text
     * @return string
     */
    public function bgCyan($text)
    {
        return "\x1B[46m{$text}\x1B[49m";
    }

    /**
     * Set the text background to white.
     *
     * @param  string  $text
     * @return string
     */
    public function bgWhite($text)
    {
        return "\x1B[47m{$text}\x1B[49m";
    }

    /**
     * Set the text color to gray.
     *
     * @param  string  $text
     * @return string
     */
    public function gray($text)
    {
        return "\x1B[90m{$text}\x1B[39m";
    }
}
