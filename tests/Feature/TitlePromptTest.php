<?php

use Laravel\Prompts\Prompt;

use function Laravel\Prompts\title;

it('updates the title', function () {
    Prompt::fake();

    title('Hello, World!');

    Prompt::assertOutputContains("\033]0;Hello, World!\007");
});
