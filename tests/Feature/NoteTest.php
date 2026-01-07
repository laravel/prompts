<?php

use Laravel\Prompts\Note;
use Laravel\Prompts\Prompt;

use function Laravel\Prompts\note;

it('renders a note', function () {
    Prompt::fake();

    note('Hello, World!');

    Prompt::assertOutputContains('Hello, World!');
});

it('can fall back', function () {
    Prompt::fallbackWhen(true);

    Note::fallbackUsing(function (Note $note) {
        expect($note->message)->toBe('Hello, World!');

        return true;
    });

    $result = (new Note('Hello, World!'))->display();

    expect($result)->toBeNull();
});
