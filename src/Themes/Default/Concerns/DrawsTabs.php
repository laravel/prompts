<?php

namespace Laravel\Prompts\Themes\Default\Concerns;

use Illuminate\Support\Collection;

trait DrawsTabs
{
    use HandlesStrings;

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

        $top_row = $tabs->map(fn($value, $key) => $key === $selected
            ? '╭' . str_repeat('─', $strippedWidth($value) + 2) . '╮'
            : str_repeat(' ', $strippedWidth($value) + 4)
        )->implode('');

        $middle_row = $tabs->map(fn($value, $key) => $key === $selected
            ? "{$this->dim('│')} {$this->{$color}($value)} {$this->dim('│')}"
            : "  {$value}  "
        )->implode('');

        $bottom_row = $tabs->map(fn($value, $key) => $key === $selected
            ? '┴' . str_repeat('─', $strippedWidth($value) + 2) . '┴'
            : str_repeat('─', $strippedWidth($value) + 4)
        )->implode('');
        $bottom_row = $this->pad($bottom_row, $width, '─');

        // automatic horizontal tab scrolling
        if ($strippedWidth($top_row) > $width) {
            $chars_to_kill = $strippedWidth($top_row) - $width;
            $percent = $selected / ($tabs->count() - 1);
            $left = (int) round($percent * $chars_to_kill);
            foreach ([&$top_row, &$middle_row, &$bottom_row] as &$row) {
                $row = mb_substr($row, $left, mb_strwidth($row) - $chars_to_kill);
            }
        }

        return collect([$this->dim($top_row), $middle_row, $this->dim($bottom_row)])->implode(PHP_EOL);
    }
}
