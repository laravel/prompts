<?php

namespace Laravel\Prompts\Themes\Terkelg;

use Laravel\Prompts\Concerns\Colors;
use Laravel\Prompts\Note;

class NoteRenderer
{
    use Colors;

    public function __invoke(Note $note)
    {
        return match ($note->type) {
            'intro' => <<<EOT
                {$this->green('✔')} {$note->message}

                EOT,

            default => <<<EOT
                {$this->green('✔')} {$note->message}

                EOT,
        };
    }
}
