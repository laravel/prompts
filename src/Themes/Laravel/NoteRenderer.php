<?php

namespace Laravel\Prompts\Themes\Laravel;

use Laravel\Prompts\Concerns\Colors;
use Laravel\Prompts\Note;

class NoteRenderer
{
    use Colors;

    public function __invoke(Note $note)
    {
        $lines = collect(explode(PHP_EOL, $note->message));

        switch ($note->type) {
            case 'intro':
            case 'outro';
                $lines = $lines->map(fn ($line) => " {$line} ");
                $longest = $lines->map(fn ($line) => strlen($line))->max();

                return PHP_EOL . $lines
                    ->map(function ($line, $i) use ($longest) {
                        $line = str_pad($line, $longest, ' ');

                        return " {$this->bgCyan($this->black($line))}";
                    })
                    ->implode(PHP_EOL) . PHP_EOL . PHP_EOL;

            default:
                return PHP_EOL . $lines
                    ->map(fn ($line, $i) => ' ' . $this->gray(match (true) {
                        count($lines) === 1 => ' ',
                        $i === 0 => '┌',
                        $i === count($lines) - 1 => '└',
                        default => '│',
                    }) . "  {$line}")
                    ->implode(PHP_EOL) . PHP_EOL;
        }
    }
}
