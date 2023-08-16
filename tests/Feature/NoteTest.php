<?php

use Laravel\Prompts\Prompt;

use function Laravel\Prompts\note;

it('renders a note', function () {
    Prompt::fake();

    note('Hello, World!');

    Prompt::assertOutputContains('Hello, World!');
});
