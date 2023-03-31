<?php

use function Laravel\Prompts\note;
use Laravel\Prompts\Prompt;

it('renders a note', function () {
    Prompt::fake([])
        ->expects('write')
        ->with(Mockery::on(fn ($text) => str_contains($text, 'Hello, World!')));

    note('Hello, World!');
});
