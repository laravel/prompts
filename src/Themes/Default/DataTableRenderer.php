<?php

namespace Laravel\Prompts\Themes\Default;

use Laravel\Prompts\DataTablePrompt;
use Laravel\Prompts\Output\BufferedConsoleOutput;
use Laravel\Prompts\Themes\Contracts\Scrolling;
use Laravel\Prompts\Themes\Default\Concerns\DrawsBoxes;
use Laravel\Prompts\Themes\Default\Concerns\DrawsScrollbars;
use Symfony\Component\Console\Helper\Table as SymfonyTable;
use Symfony\Component\Console\Helper\TableStyle;

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

        $body = $this->renderSearchLine($prompt, $maxWidth);
        $tableBody = $this->renderTable($prompt, $visible, $total);

        if ($body !== '') {
            $body .= PHP_EOL;
        }

        $body .= $tableBody;

        $firstRow = $prompt->firstVisible + 1;
        $lastRow = min($prompt->firstVisible + $prompt->scroll, $total);
        $info = '';

        if ($total > 0) {
            $suffix = $prompt->searchValue() !== '' ? ' results' : '';
            $info = $this->dim("Viewing ") . "{$firstRow}-{$lastRow}" . $this->dim(" of ") . "{$total}{$suffix}";
        }

        return $this
            ->box(
                $this->cyan($this->truncate($prompt->label, $maxWidth)),
                $body,
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

        return '';
    }

    /**
     * Render the table with visible rows.
     *
     * @param  array<int|string, array<int, string>>  $visible
     */
    protected function renderTable(DataTablePrompt $prompt, array $visible, int $total): string
    {
        if ($total === 0) {
            $message = $prompt->searchValue() !== '' ? 'No results found.' : 'No rows.';

            return $this->dim($message);
        }

        $tableLines = $this->table($prompt, $visible, $prompt->headers);

        // Identify the data row lines (not border/header lines) for scrollbar
        $dataLineIndices = $this->getDataLineIndices($tableLines, count($visible));
        $dataLines = [];
        $borderLines = [];

        foreach ($tableLines as $i => $line) {
            if (in_array($i, $dataLineIndices)) {
                $dataLines[$i] = $line;
            } else {
                $borderLines[$i] = $line;
            }
        }

        // Apply scrollbar to data lines
        if (count($dataLines) > 0) {
            $width = mb_strwidth($this->stripEscapeSequences($tableLines[0]));
            $scrolled = $this->scrollbar(
                array_values($dataLines),
                $prompt->firstVisible,
                count($dataLines),
                $this->getTotalDataLines($prompt, $total),
                $width,
            );

            $scrolledIndex = 0;
            foreach ($dataLineIndices as $i) {
                $tableLines[$i] = $scrolled[$scrolledIndex++];
            }
        }

        return implode(PHP_EOL, $tableLines);
    }

    /**
     * Get the total number of data lines (accounting for all rows, not just visible).
     */
    protected function getTotalDataLines(DataTablePrompt $prompt, int $totalRows): int
    {
        // Each row contributes at least one data line.
        // For simple cases, this is just the total number of rows.
        // The scroll height is the number of visible data lines.
        return $totalRows;
    }

    /**
     * Get the indices of data row lines in the table output.
     *
     * @param  array<int, string>  $tableLines
     * @return array<int>
     */
    protected function getDataLineIndices(array $tableLines, int $visibleCount): array
    {
        $indices = [];

        // Data lines are lines containing │ that are not border lines (─, ┼, etc.)
        foreach ($tableLines as $i => $line) {
            $stripped = $this->stripEscapeSequences($line);
            if (str_contains($stripped, '│') && ! preg_match('/[─┼┬┴╭╮╰╯├┤┘└┌┐]/', $stripped)) {
                $indices[] = $i;
            }
        }

        // If we have headers, the first data-like lines are headers, not data rows.
        // We need to figure out how many are actual data rows.
        // Simple heuristic: take only the last $visibleCount lines.
        if (count($indices) > $visibleCount) {
            $indices = array_slice($indices, -$visibleCount);
        }

        return $indices;
    }

    /**
     * Render a Symfony table to an array of lines.
     *
     * @param  array<int, array<int, string>>  $rows
     * @param  array<int, string|array<int, string>>  $headers
     * @return array<int, string>
     */
    protected function table(DataTablePrompt $prompt, array $rows, array $headers): array
    {
        $formattedRows = collect($rows)
            ->map(
                fn($row) => collect($row)
                    ->map(fn($cell) => ' ' . $cell . ' ')
                    ->map(
                        fn($cell, $index) =>
                        mb_strlen($this->stripEscapeSequences($cell)) >= $prompt->columnWidths[$index]
                            ? $this->truncate($cell, $prompt->columnWidths[$index])
                            : $this->pad($cell, $prompt->columnWidths[$index])
                    ),
            );

        $table = [];
        $table[] = $this->dim(collect($headers)->map(fn($header, $index) => $this->pad(' ' . $header . ' ', $prompt->columnWidths[$index]))->implode('│'));
        $table[] = $this->dim(collect($headers)->map(fn($header, $index) => str_repeat('─', $prompt->columnWidths[$index]))->implode('┼'));

        $highlightedKey = array_keys($prompt->filteredRows())[$prompt->highlighted] ?? null;

        foreach ($formattedRows as $key => $row) {
            $table[] = collect($row)
                ->when($key === $highlightedKey, fn($cells) => $cells->map(fn($cell) => $this->inverse($cell)))
                ->implode($key === $highlightedKey ? $this->inverse('│') : $this->dim('│'));
        }

        return $table;



        // $tableStyle = (new TableStyle)
        //     ->setHorizontalBorderChars('─')
        //     ->setVerticalBorderChars('│', '│')
        //     ->setCellHeaderFormat('<fg=default>%s</>')
        //     ->setCellRowFormat('<fg=default>%s</>');

        // if (empty($headers)) {
        //     $tableStyle->setCrossingChars('┼', '', '', '', '┤', '┘', '┴', '└', '├', '╭', '┬', '╮');
        // } else {
        //     $tableStyle->setCrossingChars('┼', '╭', '┬', '╮', '┤', '╯', '┴', '╰', '├');
        // }

        // $buffered = new BufferedConsoleOutput;

        // (new SymfonyTable($buffered))
        //     ->setHeaders($headers)
        //     ->setRows($rows)
        //     ->setStyle($tableStyle)
        //     ->render();

        // return explode(PHP_EOL, trim($buffered->content(), PHP_EOL));
    }

    /**
     * The number of lines to reserve outside of the scrollable area.
     */
    public function reservedLines(): int
    {
        return 10;
    }
}
