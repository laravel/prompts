<?php

use Laravel\Prompts\P;
use Laravel\Prompts\Prompt;

it('renders a note', function () {
    Prompt::fake();

    P::note('Hello, World!');

    Prompt::assertOutputContains('Hello, World!');
});
