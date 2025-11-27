<?php

use Laravel\Prompts\Prompt;

use function Laravel\Prompts\href;

it('renders a link', function () {
    Prompt::fake();

    href('Hello, World!', 'https://example.com', 'title');

    Prompt::assertStrippedOutputContains('Hello, World!');
    Prompt::assertStrippedOutputContains('https://example.com');
    Prompt::assertStrippedOutputContains('title');
});

it('renders a link without tooltip', function () {
    Prompt::fake();

    href('Hello, World!', 'https://example.com');

    Prompt::assertStrippedOutputContains('Hello, World!');
    Prompt::assertStrippedOutputContains('https://example.com');
});
