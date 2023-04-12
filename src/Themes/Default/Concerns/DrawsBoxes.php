<?php

namespace Laravel\Prompts\Themes\Default\Concerns;

trait DrawsBoxes
{
    protected int $minWidth = 60;

    /**
     * Draw a box.
     */
    protected function box(
        string $title,
        string $body,
        string $footer = '',
        string $color = 'gray',
    ): string {
        $bodyLines = collect(explode(PHP_EOL, $body));
        $footerLines = collect(explode(PHP_EOL, $footer))->filter();
        $width = $this->longest(
            $bodyLines
                ->merge($footerLines)
                ->push($title)
                ->toArray()
        );

        $topBorder = str_repeat('─', $width - mb_strlen($this->stripEscapeSequences($title)));
        $bottomBorder = str_repeat('─', $width + 2);

        $top = "{$this->{$color}(' ┌')} {$title} {$this->{$color}($topBorder.'┐')}";
        $bodyLines = $bodyLines->map(function ($line) use ($width, $color) {
            return "{$this->{$color}(' │')} {$this->pad($line, $width)} {$this->{$color}('│')}";
        });
        $divider = $this->{$color}(' ├'.$bottomBorder.'┤');
        $footerLines = $footerLines->map(function ($line) use ($width, $color) {
            return "{$this->{$color}(' │')} {$this->pad($line, $width)} {$this->{$color}('│')}";
        });
        $bottom = $this->{$color}(' └'.$bottomBorder.'┘');

        if ($footerLines->isNotEmpty()) {
            return $top.PHP_EOL.$bodyLines->implode(PHP_EOL).PHP_EOL.$divider.PHP_EOL.$footerLines->implode(PHP_EOL).PHP_EOL.$bottom;
        }

        return $top.PHP_EOL.$bodyLines->implode(PHP_EOL).PHP_EOL.$bottom;
    }

    /**
     * Get the length of the longest line.
     *
     * @param  array<string>  $lines
     */
    protected function longest(array $lines, int $padding = 0): int
    {
        return max(
            $this->minWidth,
            collect($lines)
                ->map(fn ($line) => mb_strlen($this->stripEscapeSequences($line)) + $padding)
                ->max()
        );
    }

    /**
     * Pad text ignoring ANSI escape sequences.
     */
    protected function pad(string $text, int $length): string
    {
        $rightPadding = str_repeat(' ', $length - mb_strlen($this->stripEscapeSequences($text)));

        return "{$text}{$rightPadding}";
    }

    /**
     * Strip ANSI escape sequences from the given text.
     */
    protected function stripEscapeSequences(string $text): string
    {
        return preg_replace("/\e[^m]*m/", '', $text);
    }
}
