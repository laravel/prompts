<?php

namespace Laravel\Prompts\Themes\Default;

use Laravel\Prompts\Concerns\Colors;
use Laravel\Prompts\Note;

class NoteRenderer
{
    use Colors;

    /**
     * Render the note.
     */
    public function __invoke(Note $note): string
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
                    ->implode(PHP_EOL) . str_repeat(PHP_EOL, $note->type === 'intro' ? 1 : 2);

            default:
                return PHP_EOL . $lines
                    ->map(fn ($line) => " {$line}")
                    ->implode(PHP_EOL) . PHP_EOL;
        }
    }
}
