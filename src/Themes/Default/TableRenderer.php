<?php

namespace Laravel\Prompts\Themes\Default;

use Laravel\Prompts\Output\BufferedConsoleOutput;
use Laravel\Prompts\Table;
use Symfony\Component\Console\Helper\Table as SymfonyTable;
use Symfony\Component\Console\Helper\TableStyle;

class TableRenderer extends Renderer
{
    /**
     * Render the table.
     */
    public function __invoke(Table $table): string
    {
        $tableStyle = (new TableStyle())
            ->setHorizontalBorderChars('─')
            ->setVerticalBorderChars(' │', '│')
            ->setCrossingChars('┼', ' ┌', '┬', '─┐', '─┤', '─┘', '┴', ' └', ' ├')
            ->setCellHeaderFormat($this->dim('%s'))
            ->setBorderFormat($this->dim('%s'));

        $buffered = new BufferedConsoleOutput();

        (new SymfonyTable($buffered))
            ->setHeaders($table->headers)
            ->setRows($table->rows)
            ->setStyle($tableStyle)
            ->render();

        collect(explode(PHP_EOL, $buffered->content()))
            ->filter(fn ($line) => $line !== '')
            ->each(fn ($line) => $this->line($line));

        return $this;
    }
}
