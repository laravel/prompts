<?php

use Laravel\Prompts\Prompt;

use function Laravel\Prompts\link;

it('renders a link', function () {
    Prompt::fake();

    link('Hello, World!', 'https://example.com', 'title');

    Prompt::assertStrippedOutputContains('Hello, World!');
    Prompt::assertStrippedOutputContains('https://example.com');
    Prompt::assertStrippedOutputContains('title');
});
