<?php

use function Laravel\Prompts\note;
use Laravel\Prompts\Prompt;

it('renders a note', function () {
    Prompt::fake();

    note('Hello, World!');

    Prompt::assertOutputContains('Hello, World!');
});
