<?php

use Laravel\Prompts\Prompt;

use function Laravel\Prompts\clear;

it('clears', function () {
    Prompt::fake();

    clear();

    Prompt::assertOutputContains("\033[H\033[J");
});
