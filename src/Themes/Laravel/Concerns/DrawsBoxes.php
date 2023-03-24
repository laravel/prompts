<?php

namespace Laravel\Prompts\Themes\Laravel\Concerns;

trait DrawsBoxes
{
    /**
     * Draw a box.
     *
     * @param  string  $title
     * @param  string  $body
     * @param  string  $borderColor
     * @return string
     */
    protected function box($title, $body, $borderColor = 'gray', $minWidth = 60)
    {
        $lines = collect(explode(PHP_EOL, $body));
        $longestLineLength = $lines->map(fn ($line) => mb_strlen($this->stripEscapeSequences($line)))->max();

        $length = max($minWidth, mb_strlen($this->stripEscapeSequences($title)), $longestLineLength);
        $topBorder = str_repeat('─', $length - mb_strlen($this->stripEscapeSequences($title)));
        $bottomBorder = str_repeat('─', $length + 2);

        $top = "{$this->{$borderColor}(' ┌')} {$title} {$this->{$borderColor}($topBorder.'┐')}";
        $lines = $lines->map(function ($line) use ($length, $borderColor) {
            $rightPadding = str_repeat(' ', $length - mb_strlen($this->stripEscapeSequences($line)));

            return "{$this->{$borderColor}(' │')} {$line} {$rightPadding}{$this->{$borderColor}('│')}";
        });
        $bottom = $this->{$borderColor}(' └'.$bottomBorder.'┘');

        return $top.PHP_EOL.$lines->implode(PHP_EOL).PHP_EOL.$bottom;
    }

    /**
     * Strip ANSI escape sequences from the given text.
     *
     * @param  string  $text
     * @return string
     */
    protected function stripEscapeSequences($text)
    {
        return preg_replace('/\x1b[^m]*m/', '', $text);
    }
}
