<?php

namespace Laravel\Prompts\Themes\Default;

use Laravel\Prompts\DataTablePrompt;
use Laravel\Prompts\Themes\Contracts\Scrolling;
use Laravel\Prompts\Themes\Default\Concerns\DrawsBoxes;
use Laravel\Prompts\Themes\Default\Concerns\DrawsScrollbars;

class DataTableRenderer extends Renderer implements Scrolling
{
    use DrawsBoxes;
    use DrawsScrollbars;

    /**
     * Render the data table.
     */
    public function __invoke(DataTablePrompt $prompt): string
    {
        $maxWidth = $prompt->terminal()->cols() - 6;

        return match ($prompt->state) {
            'submit' => $this->renderSubmit($prompt, $maxWidth),
            'cancel' => $this->renderCancel($prompt, $maxWidth),
            default => $this->renderActive($prompt, $maxWidth),
        };
    }

    /**
     * Render the submit state.
     */
    protected function renderSubmit(DataTablePrompt $prompt, int $maxWidth): string
    {
        $row = $prompt->selectedRow();
        $display = $row ? $this->truncate(implode(', ', $row), $maxWidth) : '';

        return $this
            ->box(
                $this->dim($this->truncate($prompt->label, $maxWidth)),
                $display,
            );
    }

    /**
     * Render the cancel state.
     */
    protected function renderCancel(DataTablePrompt $prompt, int $maxWidth): string
    {
        $row = $prompt->selectedRow();
        $display = $row ? $this->dim($this->strikethrough($this->truncate(implode(', ', $row), $maxWidth))) : '';

        return $this
            ->box(
                $this->truncate($prompt->label, $maxWidth),
                $display,
                color: 'red',
            )
            ->error($prompt->cancelMessage);
    }

    /**
     * Render the active/browse/search state.
     */
    protected function renderActive(DataTablePrompt $prompt, int $maxWidth): string
    {
        $filtered = $prompt->filteredRows();
        $total = count($filtered);
        $visible = $prompt->visible();

        $firstRow = $prompt->firstVisible + 1;
        $lastRow = min($prompt->firstVisible + $prompt->scroll, $total);
        $info = '';

        if ($total > 0) {
            $suffix = $prompt->searchValue() !== '' ? ' results' : '';
            $info = $this->dim('Viewing ') . $firstRow . '-' . $lastRow . $this->dim(' of ') . $total . $suffix;
        }

        return $this
            ->box(
                $this->cyan($this->truncate($prompt->label, $maxWidth)),
                body: $this->renderTable($prompt, $filtered, $visible, $maxWidth),
                footer: $this->renderSearchLine($prompt, $maxWidth),
                info: $info,
            )
            ->when(
                $prompt->state === 'error',
                fn() => $this->warning($this->truncate($prompt->error, $prompt->terminal()->cols() - 5)),
                fn() => $this->when(
                    $prompt->hint,
                    fn() => $this->hint($prompt->hint),
                    fn() => $this->newLine(),
                ),
            );
    }

    /**
     * Render the search line above the table.
     */
    protected function renderSearchLine(DataTablePrompt $prompt, int $maxWidth): string
    {
        if ($prompt->state === 'search') {
            return $this->cyan('/') . ' ' . $prompt->searchWithCursor($maxWidth - 4);
        }

        if ($prompt->searchValue() !== '') {
            return $this->dim('/') . ' ' . $prompt->searchValue();
        }

        return $this->dim('/ to search');
    }

    /**
     * Render the table with visible rows.
     *
     * @param  array<int|string, array<int, string>>  $filtered
     * @param  array<int|string, array<int, string>>  $visible
     */
    protected function renderTable(DataTablePrompt $prompt, array $filtered, array $visible, int $maxWidth): string
    {
        $total = count($filtered);

        if ($total === 0) {
            $message = $prompt->searchValue() !== '' ? 'No results found.' : 'No rows.';

            return $this->dim($message);
        }

        $numCols = ! empty($prompt->headers)
            ? count($prompt->headers)
            : max(array_map('count', $prompt->rows));

        // Compute column widths from ALL rows (not filtered) to prevent layout shift when searching
        $widths = $this->computeColumnWidths($prompt->headers, $prompt->rows, $numCols, $maxWidth);

        $highlightedKey = array_keys($filtered)[$prompt->highlighted] ?? null;

        $lines = [];

        // Header
        if (! empty($prompt->headers)) {
            $headerCells = [];

            foreach ($widths as $i => $w) {
                $header = $prompt->headers[$i] ?? '';
                $text = is_array($header) ? implode(' ', $header) : $header;
                $headerCells[] = $this->dim(' ' . $this->pad($this->truncate($text, $w), $w) . ' ');
            }

            $lines[] = implode($this->gray('│'), $headerCells) . ' ';
            $lines[] = $this->gray(implode('┼', array_map(fn($w) => str_repeat('─', $w + 2), $widths))) . ' ';
        }

        // Data rows — expand multiline cells into sub-rows
        $dataLines = [];

        foreach ($visible as $key => $row) {
            $isHighlighted = $key === $highlightedKey;

            // Split each cell by newlines
            $cellLines = [];
            $maxSubRows = 1;

            foreach ($widths as $i => $w) {
                $text = $row[$i] ?? '';
                $subLines = explode(PHP_EOL, $text);
                $cellLines[$i] = $subLines;
                $maxSubRows = max($maxSubRows, count($subLines));
            }

            // Render each sub-row
            for ($subRow = 0; $subRow < $maxSubRows; $subRow++) {
                $cells = [];

                foreach ($widths as $i => $w) {
                    $text = $cellLines[$i][$subRow] ?? '';
                    $content = ' ' . $this->pad($this->truncate($text, $w), $w) . ' ';

                    if ($isHighlighted) {
                        $content = $this->inverse($content);
                    }

                    $cells[] = $content;
                }

                $separator = $isHighlighted ? $this->inverse('│') : $this->dim('│');
                $dataLines[] = implode($separator, $cells) . ' ';
            }
        }

        // Apply scrollbar to data lines
        $tableWidth = array_sum($widths) + ($numCols - 1) + ($numCols * 2) + 1;
        $visibleDataLineCount = count($dataLines);
        $dataLines = $this->scrollbar(
            $dataLines,
            $prompt->firstVisible,
            $visibleDataLineCount,
            $total,
            $tableWidth,
        );

        $lines = array_merge($lines, $dataLines);

        return implode(PHP_EOL, $lines);
    }

    /**
     * Compute column widths that fit within maxWidth.
     *
     * Columns that fit at their natural width get it; overflowing columns
     * share the remaining space proportionally.
     *
     * @param  array<int, string|array<int, string>>  $headers
     * @param  array<int|string, array<int, string>>  $allRows
     * @return array<int, int>
     */
    protected function computeColumnWidths(array $headers, array $allRows, int $numCols, int $maxWidth): array
    {
        // Natural width = max cell content width per column (across all rows + header)
        $natural = array_fill(0, $numCols, 0);

        foreach ($headers as $i => $header) {
            $headerText = is_array($header) ? implode(' ', $header) : $header;
            $natural[$i] = max($natural[$i], mb_strwidth($headerText));
        }

        foreach ($allRows as $row) {
            foreach ($row as $i => $cell) {
                // Measure each line individually for multiline cells
                foreach (explode(PHP_EOL, $cell) as $line) {
                    $natural[$i] = max($natural[$i], mb_strwidth($line));
                }
            }
        }

        // Available width for cell content:
        // Each column has 1 space padding on each side = 2 per column
        // Columns separated by │ = numCols - 1 separators
        // Scrollbar takes 1 char on the right
        $overhead = ($numCols * 2) + ($numCols - 1) + 1;
        $available = $maxWidth - $overhead;

        if ($available <= 0) {
            return array_fill(0, $numCols, 1);
        }

        // If everything fits, use natural widths
        if (array_sum($natural) <= $available) {
            return $natural;
        }

        // Smart allocation: give fitting columns their natural width,
        // then split remaining space proportionally among overflowing columns
        $widths = $natural;
        $remaining = $available;
        $unresolved = range(0, $numCols - 1);

        while (count($unresolved) > 0) {
            $fairShare = $remaining / count($unresolved);
            $newlyResolved = [];

            foreach ($unresolved as $i) {
                if ($natural[$i] <= $fairShare) {
                    $widths[$i] = $natural[$i];
                    $remaining -= $natural[$i];
                    $newlyResolved[] = $i;
                }
            }

            if (empty($newlyResolved)) {
                // All remaining columns overflow — split proportionally
                $totalNatural = array_sum(array_map(fn($i) => $natural[$i], $unresolved));

                foreach ($unresolved as $i) {
                    $widths[$i] = max(1, (int) floor($remaining * $natural[$i] / $totalNatural));
                }

                break;
            }

            $unresolved = array_values(array_diff($unresolved, $newlyResolved));
        }

        return $widths;
    }

    /**
     * The number of lines to reserve outside of the scrollable area.
     */
    public function reservedLines(): int
    {
        return 10;
    }
}
