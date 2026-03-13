<?php

namespace Laravel\Prompts\Concerns;

trait Colors
{
    /**
     * Reset all colors and styles.
     */
    public function reset(string $text): string
    {
        return "\e[0m{$text}\e[0m";
    }

    /**
     * Make the text bold.
     */
    public function bold(string $text): string
    {
        return "\e[1m{$text}\e[22m";
    }

    /**
     * Make the text dim.
     */
    public function dim(string $text): string
    {
        return "\e[2m{$text}\e[22m";
    }

    /**
     * Make the text italic.
     */
    public function italic(string $text): string
    {
        return "\e[3m{$text}\e[23m";
    }

    /**
     * Underline the text.
     */
    public function underline(string $text): string
    {
        return "\e[4m{$text}\e[24m";
    }

    /**
     * Invert the text and background colors.
     */
    public function inverse(string $text): string
    {
        return "\e[7m{$text}\e[27m";
    }

    /**
     * Hide the text.
     */
    public function hidden(string $text): string
    {
        return "\e[8m{$text}\e[28m";
    }

    /**
     * Strike through the text.
     */
    public function strikethrough(string $text): string
    {
        return "\e[9m{$text}\e[29m";
    }

    /**
     * Set the text color to black.
     */
    public function black(string $text): string
    {
        return "\e[30m{$text}\e[39m";
    }

    /**
     * Set the text color to red.
     */
    public function red(string $text): string
    {
        return "\e[31m{$text}\e[39m";
    }

    /**
     * Set the text color to green.
     */
    public function green(string $text): string
    {
        return "\e[32m{$text}\e[39m";
    }

    /**
     * Set the text color to yellow.
     */
    public function yellow(string $text): string
    {
        return "\e[33m{$text}\e[39m";
    }

    /**
     * Set the text color to blue.
     */
    public function blue(string $text): string
    {
        return "\e[34m{$text}\e[39m";
    }

    /**
     * Set the text color to magenta.
     */
    public function magenta(string $text): string
    {
        return "\e[35m{$text}\e[39m";
    }

    /**
     * Set the text color to cyan.
     */
    public function cyan(string $text): string
    {
        return "\e[36m{$text}\e[39m";
    }

    /**
     * Set the text color to white.
     */
    public function white(string $text): string
    {
        return "\e[37m{$text}\e[39m";
    }

    /**
     * Set the text background to black.
     */
    public function bgBlack(string $text): string
    {
        return "\e[40m{$text}\e[49m";
    }

    /**
     * Set the text background to red.
     */
    public function bgRed(string $text): string
    {
        return "\e[41m{$text}\e[49m";
    }

    /**
     * Set the text background to green.
     */
    public function bgGreen(string $text): string
    {
        return "\e[42m{$text}\e[49m";
    }

    /**
     * Set the text background to yellow.
     */
    public function bgYellow(string $text): string
    {
        return "\e[43m{$text}\e[49m";
    }

    /**
     * Set the text background to blue.
     */
    public function bgBlue(string $text): string
    {
        return "\e[44m{$text}\e[49m";
    }

    /**
     * Set the text background to magenta.
     */
    public function bgMagenta(string $text): string
    {
        return "\e[45m{$text}\e[49m";
    }

    /**
     * Set the text background to cyan.
     */
    public function bgCyan(string $text): string
    {
        return "\e[46m{$text}\e[49m";
    }

    /**
     * Set the text background to white.
     */
    public function bgWhite(string $text): string
    {
        return "\e[47m{$text}\e[49m";
    }

    /**
     * Set the text color to gray.
     */
    public function gray(string $text): string
    {
        return "\e[90m{$text}\e[39m";
    }

    /**
     * Get an array of closures that progressively fade text from full color to nearly invisible.
     *
     * @return array<int, \Closure(string): string>
     */
    public function fadeOut($steps = 10): array
    {
        $terminal = static::terminal();

        if (!$terminal->supportsTrueColor()) {
            return [
                fn(string $text) => $text,
                fn(string $text) => $this->dim($text),
            ];
        }

        $fg = $terminal->foregroundColor();
        $bg = $terminal->backgroundColor();

        return array_map(
            function (int $step) use ($fg, $bg, $steps) {
                $factor = 1 - ($step / $steps); // 1.0 → 0.2
                $r = (int) ($bg[0] + ($fg[0] - $bg[0]) * $factor);
                $g = (int) ($bg[1] + ($fg[1] - $bg[1]) * $factor);
                $b = (int) ($bg[2] + ($fg[2] - $bg[2]) * $factor);

                return fn(string $text) => "\e[38;2;{$r};{$g};{$b}m{$text}\e[0m";
            },
            range(0, $steps - 1),
        );
    }
}
