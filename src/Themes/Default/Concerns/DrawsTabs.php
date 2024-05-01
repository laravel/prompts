<?php

namespace Laravel\Prompts\Themes\Default\Concerns;

use Illuminate\Support\Collection;

trait DrawsTabs
{
    use InteractsWithStrings;

    /**
     * Render a row of tabs.
     *
     * @param Collection<int, string>  $tabs
     */
    protected function tabs(
        Collection $tabs,
        int $selected,
        int $width,
        string $color = 'cyan',
    ): string {
        $strippedWidth = fn (string $value): int => mb_strwidth($this->stripEscapeSequences($value));

        // Build the top row for the tabs by adding whitespace equal
        // to the width of each tab plus padding, or by adding an
        // equal number of box characters for the selected tab.
        $top_row = $tabs->map(fn($value, $key) => $key === $selected
            ? '╭' . str_repeat('─', $strippedWidth($value) + 2) . '╮'
            : str_repeat(' ', $strippedWidth($value) + 4)
        )->implode('');

        // Build the middle row for the tabs by adding the tab name
        // surrounded by some padding. But if the tab is selected
        // then highlight the tab and surround it in box chars.
        $middle_row = $tabs->map(fn($value, $key) => $key === $selected
            ? "{$this->dim('│')} {$this->{$color}($value)} {$this->dim('│')}"
            : "  {$value}  "
        )->implode('');

        // Build the bottom row for the tabs by adding box characters equal to the width
        // of each tab, plus padding. If the tab is selected, add the appropriate box
        // characters instead. Finally, pad the whole line to fill the width fully.
        $bottom_row = $tabs->map(fn($value, $key) => $key === $selected
            ? '┴' . str_repeat('─', $strippedWidth($value) + 2) . '┴'
            : str_repeat('─', $strippedWidth($value) + 4)
        )->implode('');
        $bottom_row = $this->pad($bottom_row, $width, '─');

        // If the tabs are wider than the provided width, we need to trim the tabs to fit.
        // We remove the appropriate number of characters from the beginning and end of
        // each row by using the highlighted tab's index to get it's scroll position.
        if ($strippedWidth($top_row) > $width) {
            $scroll = $selected / ($tabs->count() - 1);
            $chars_to_kill = $strippedWidth($top_row) - $width;
            $offset = (int) round($scroll * $chars_to_kill);
            foreach ([&$top_row, &$middle_row, &$bottom_row] as &$row) {
                $row = mb_substr($row, $offset, mb_strwidth($row) - $chars_to_kill);
            }
        }

        // We wait until now to dim the top and bottom
        // rows, otherwise the horizontal scrolling
        // could easily strip those instructions.
        return collect([$this->dim($top_row), $middle_row, $this->dim($bottom_row)])->implode(PHP_EOL);
    }
}
