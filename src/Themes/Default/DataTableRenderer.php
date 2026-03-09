<?php

namespace Laravel\Prompts\Themes\Default;

use Laravel\Prompts\Output\BufferedConsoleOutput;
use Laravel\Prompts\Themes\Default\Concerns\InteractsWithStrings;
use Symfony\Component\Console\Helper\Table as SymfonyTable;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Helper\TableCellStyle;
use Symfony\Component\Console\Helper\TableStyle;

class DataTableRenderer extends Renderer
{
    use InteractsWithStrings;

    protected int $tableWidth = 0;

    /**
     * Render the data table.
     */
    public function __invoke(DataTabl $prompt): string
    {
        $selectedStyle = new TableCellStyle([
            'bg' => 'white',
            'fg' => 'black',
        ]);

        $tableLines = [];

        $rows = $prompt->visible();

        if (count($rows) > 0) {
            if ($prompt->state === 'browse') {
                $rows[$prompt->index] = collect($rows[$prompt->index])->map(
                    fn($cell) => new TableCell($cell, ['style' => $selectedStyle]),
                )->all();
            }

            $tableLines = $this->table($prompt, $rows, $prompt->headers);
            $this->tableWidth = mb_strwidth($this->stripEscapeSequences($tableLines[0])) - 1;
        }

        $this->renderSearch($prompt);
        $this->renderJump($prompt);

        if ($this->output === '') {
            $this->withPageCount($prompt, '');
        }

        $tableLinesCount = count($tableLines);

        if ($tableLinesCount > 0) {
            $first = true;

            foreach ($tableLines as $line) {
                if ($first) {
                    $this->bullet($line);
                } else {
                    $this->lineWithBorder($line);
                }

                $first = false;
            }
        } else {
            $this->bullet($this->dim(' No results found.'));
            $tableLinesCount++;
        }

        $i = $tableLinesCount;

        // Per page + top line + header line + divider line + bottom line
        while ($i < $prompt->perPage + 4) {
            $this->lineWithBorder('');
            $i++;
        }

        foreach ($prompt->keyBindingsHelp->get() as $line) {
            $this->lineWithBorder(' ' . $line);
        }

        return $this;
    }

    protected function table(DataTable $prompt, $rows, $headers): array
    {

        $tableStyle = (new TableStyle)
            ->setHorizontalBorderChars('─')
            ->setVerticalBorderChars('│', '│')
            ->setCellHeaderFormat('<fg=default>%s</>');

        if ($prompt->state === 'search') {
            $tableStyle->setCellRowFormat('<fg=gray>%s</>');
        } else {
            $tableStyle->setCellRowFormat('<fg=default>%s</>');
        }

        if (empty($headers)) {
            $tableStyle->setCrossingChars('┼', '', '', '', '┤', '┘</>', '┴', '└', '├', '<fg=gray>╭', '┬', '╮');
        } else {
            $tableStyle->setCrossingChars('┼', '<fg=gray>╭', '┬', '╮', '┤', '╯</>', '┴', '╰', '├');
        }

        $buffered = new BufferedConsoleOutput;

        (new SymfonyTable($buffered))
            ->setHeaders($headers)
            ->setRows($rows)
            ->setStyle($tableStyle)
            ->render();

        return explode(PHP_EOL, trim($buffered->content(), PHP_EOL));
    }

    protected function renderSearch(DataTable $prompt): void
    {

        if ($prompt->state === 'search') {
            $this->withPageCount($prompt, ' Search: ' . $prompt->valueWithCursor(60));

            return;
        }

        if ($prompt->query === '') {
            return;
        }

        if ($prompt->query !== '') {
            $this->withPageCount($prompt, ' Search: ' . $prompt->query);

            return;
        }
    }

    protected function withPageCount(DataTable $prompt, string $content): void
    {
        $pageCount = $this->tableWidth === 0 ? '' : $this->dim('Page ') . $prompt->page . $this->dim(' of ') . $prompt->totalPages;
        $pageCountWidth = mb_strwidth($this->stripEscapeSequences($pageCount));
        $contentWidth = mb_strwidth($this->stripEscapeSequences($content));

        $this->lineWithBorder(
            $content
                . str_repeat(' ', max(0, $this->tableWidth - $contentWidth - $pageCountWidth))
                . $pageCount,
        );
    }

    protected function renderJump(DataTable $prompt)
    {
        if ($prompt->state !== 'jump') {
            return;
        }

        $this->withPageCount($prompt, ' Jump to Page: ' . $prompt->jumpValueWithCursor(60));
    }
}
