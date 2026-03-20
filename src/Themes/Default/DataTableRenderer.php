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

        $numCols = ! empty($prompt->headers)
            ? count($prompt->headers)
            : max(array_map('count', $prompt->rows));

        // Compute column widths from ALL rows (not filtered) to prevent layout shift when searching
        $widths = $this->computeColumnWidths($prompt->headers, $prompt->rows, $numCols, $maxWidth);

        // Inner width between the outer │ chars:
        // cells (sum of w+2 padding each) + separators (numCols-1) + 2 (scrollbar area)
        $innerWidth = array_sum($widths) + ($numCols * 2) + ($numCols - 1) + 2;

        // Top border: ┌ Title ───┐
        $titleText = $this->cyan($this->truncate($prompt->label, $maxWidth));
        $titleLength = mb_strwidth($this->stripEscapeSequences($titleText));
        $topBorderFill = max(0, $innerWidth - $titleLength - 2);
        $this->line($this->gray(' ┌') . " {$titleText} " . $this->gray(str_repeat('─', $topBorderFill) . '┐'));

        // Search line: │ / Search              │
        $searchContent = $this->renderSearchLine($prompt, $innerWidth - 2);
        $this->line($this->gray(' │') . ' ' . $this->pad($searchContent, $innerWidth - 2) . ' ' . $this->gray('│'));

        // Column separator: ├──────┬────────┤
        $this->line(' ' . $this->renderBorder('├', '┬', '┤', $widths));

        // Header cells: │ Header │ Header   │
        if (! empty($prompt->headers)) {
            $headerCells = [];

            foreach ($widths as $i => $w) {
                $header = $prompt->headers[$i] ?? '';
                $text = is_array($header) ? implode(' ', $header) : $header;
                $headerCells[] = $this->dim(' ' . $this->pad($this->truncate($text, $w), $w) . ' ');
            }

            $headerLine = implode($this->gray('│'), $headerCells) . '  ';
            $this->line($this->gray(' │') . $this->pad($headerLine, $innerWidth) . $this->gray('│'));

            // Header separator: ├──────┼────────┤
            $this->line(' ' . $this->renderBorder('├', '┼', '┤', $widths));
        }

        // Data rows
        $dataLines = $this->renderDataRows($prompt, $filtered, $visible, $widths, $numCols, $innerWidth);

        foreach ($dataLines as $dataLine) {
            $this->line($this->gray(' │') . $this->pad($dataLine, $innerWidth) . $this->gray('│'));
        }

        // Bottom border: └──────┴────────┘
        $this->line(' ' . $this->renderBorder('└', '┴', '┘', $widths));

        // Info line below the box
        $firstRow = $prompt->firstVisible + 1;
        $lastRow = min($prompt->firstVisible + $prompt->scroll, $total);

        if ($total > 0) {
            $suffix = $prompt->searchValue() !== '' ? ' results' : '';
            $info = $this->dim('  Viewing ') . $firstRow . '-' . $lastRow . $this->dim(' of ') . $total . $suffix;
            $this->line($info);
        }

        return $this
            ->when(
                $prompt->state === 'error',
                fn () => $this->warning($this->truncate($prompt->error, $prompt->terminal()->cols() - 5)),
                fn () => $this->when(
                    $prompt->hint,
                    fn () => $this->hint($prompt->hint),
                    fn () => $this->newLine(),
                ),
            );
    }

    /**
     * Render a column-aware border line.
     *
     * @param  array<int, int>  $widths
     */
    protected function renderBorder(string $left, string $mid, string $right, array $widths): string
    {
        $segments = array_map(fn ($w) => str_repeat('─', $w + 2), $widths);

        return $this->gray($left . implode($mid, $segments) . '──' . $right);
    }

    /**
     * Render the search line content.
     */
    protected function renderSearchLine(DataTablePrompt $prompt, int $maxWidth): string
    {
        if ($prompt->state === 'search') {
            return $this->cyan('/') . ' ' . $prompt->searchWithCursor($maxWidth - 4);
        }

        if ($prompt->searchValue() !== '') {
            return $this->dim('/') . ' ' . $prompt->searchValue();
        }

        return $this->dim('/ Search');
    }

    /**
     * Render data rows with scrollbar support.
     *
     * @param  array<int|string, array<int, string>>  $filtered
     * @param  array<int|string, array<int, string>>  $visible
     * @param  array<int, int>  $widths
     * @return array<int, string>
     */
    protected function renderDataRows(DataTablePrompt $prompt, array $filtered, array $visible, array $widths, int $numCols, int $innerWidth): array
    {
        $total = count($filtered);

        // Build an empty row template for padding
        $emptyRow = implode($this->gray('│'), array_map(
            fn ($w) => str_repeat(' ', $w + 2),
            $widths,
        )) . '  ';

        $highlightedKey = array_keys($filtered)[$prompt->highlighted] ?? null;

        $dataLines = [];

        if ($total === 0) {
            $message = $prompt->searchValue() !== '' ? 'No results found.' : 'No rows.';
            $dataLines[] = $this->pad(' ' . $this->dim($message), $innerWidth);
        } else {
            $isSearching = $prompt->state === 'search';

            foreach ($visible as $key => $row) {
                $isHighlighted = ! $isSearching && $key === $highlightedKey;

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
                        } elseif ($isSearching) {
                            $content = $this->dim($content);
                        }

                        $cells[] = $content;
                    }

                    $separator = $isHighlighted ? $this->inverse('│') : $this->gray('│');
                    $dataLines[] = implode($separator, $cells) . '  ';
                }
            }
        }

        // Compute fixed minimum height from ALL rows to prevent layout shift.
        $extraLines = [];

        foreach ($prompt->rows as $row) {
            $rowLines = 1;

            foreach ($row as $cell) {
                $rowLines = max($rowLines, substr_count($cell, PHP_EOL) + 1);
            }

            $extraLines[] = $rowLines - 1;
        }

        rsort($extraLines);
        $worstCaseExtra = array_sum(array_slice($extraLines, 0, $prompt->scroll));
        $minHeight = $prompt->scroll + $worstCaseExtra;

        while (count($dataLines) < $minHeight) {
            $dataLines[] = $emptyRow;
        }

        // Apply scrollbar to data lines.
        // We can't use the trait's scrollbar() directly because it compares visual
        // line count against logical row count — multiline rows inflate visual lines
        // beyond $total, causing the scrollbar to disappear. Instead, determine
        // scrollability from logical counts and map the indicator to visual space.
        $shouldScroll = $total > $prompt->scroll;

        if ($shouldScroll) {
            $numVisual = count($dataLines);
            $maxFirst = $total - $prompt->scroll;

            if ($prompt->firstVisible === 0) {
                $visualPos = 0;
            } elseif ($prompt->firstVisible >= $maxFirst) {
                $visualPos = $numVisual - 1;
            } elseif ($numVisual <= 2) {
                $visualPos = -1;
            } else {
                $percent = $prompt->firstVisible / $maxFirst;
                $visualPos = (int) round($percent * ($numVisual - 3)) + 1;
            }

            $dataLines = array_map(fn ($line, $index) => match ($index) {
                $visualPos => preg_replace('/.$/', $this->cyan('┃'), $this->pad($line, $innerWidth)) ?? '',
                default => preg_replace('/.$/', $this->gray('│'), $this->pad($line, $innerWidth)) ?? '',
            }, array_values($dataLines), range(0, $numVisual - 1));
        }

        return $dataLines;
    }

    /**
     * Compute column widths that fit within maxWidth.
     *
     * Columns get their natural (P80) width. Only shrink proportionally
     * if the total exceeds available terminal space.
     *
     * @param  array<int, string|array<int, string>>  $headers
     * @param  array<int|string, array<int, string>>  $allRows
     * @return array<int, int>
     */
    protected function computeColumnWidths(array $headers, array $allRows, int $numCols, int $maxWidth): array
    {
        // Header widths serve as the floor for each column
        $headerWidths = array_fill(0, $numCols, 0);

        foreach ($headers as $i => $header) {
            $headerText = is_array($header) ? implode(' ', $header) : $header;
            $headerWidths[$i] = mb_strwidth($headerText);
        }

        // Collect all cell widths per column
        $columnWidths = array_fill(0, $numCols, []);

        foreach ($allRows as $row) {
            foreach ($row as $i => $cell) {
                $cellMax = 0;
                foreach (explode(PHP_EOL, $cell) as $line) {
                    $cellMax = max($cellMax, mb_strwidth($line));
                }
                $columnWidths[$i][] = $cellMax;
            }
        }

        // Natural width = P80 of cell widths, floored at header width
        $natural = array_fill(0, $numCols, 0);

        foreach ($columnWidths as $i => $widths) {
            if (empty($widths)) {
                $natural[$i] = $headerWidths[$i];
                continue;
            }

            sort($widths);
            $p85Index = (int) ceil(count($widths) * 0.85) - 1;
            $p85 = $widths[max(0, $p85Index)];
            $natural[$i] = max($headerWidths[$i], $p85);
        }

        // Available width for cell content:
        // Each column has 1 space padding on each side = 2 per column
        // Columns separated by │ = numCols - 1 separators
        // Scrollbar area = 2 chars on the right
        // Outer frame = 4 chars (` │` left + ` │` right)
        $overhead = ($numCols * 2) + ($numCols - 1) + 2 + 4;
        $available = $maxWidth - $overhead;

        if ($available <= 0) {
            return array_fill(0, $numCols, 1);
        }

        $totalNatural = array_sum($natural);

        // If natural widths fit, use them directly (comfortable width)
        if ($totalNatural <= $available) {
            return $natural;
        }

        // Otherwise, shrink proportionally
        $widths = array_fill(0, $numCols, 0);

        foreach ($natural as $i => $w) {
            $widths[$i] = max($headerWidths[$i], (int) floor($available * $w / $totalNatural));
        }

        // Distribute any remaining pixels from rounding
        $remainder = $available - array_sum($widths);

        if ($remainder > 0) {
            $order = range(0, $numCols - 1);
            usort($order, fn ($a, $b) => $natural[$b] <=> $natural[$a]);

            foreach ($order as $i) {
                if ($remainder <= 0) {
                    break;
                }
                $widths[$i]++;
                $remainder--;
            }
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
