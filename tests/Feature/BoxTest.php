<?php

use Laravel\Prompts\Prompt;

use function Laravel\Prompts\box;

it('renders a simple box', function () {
    Prompt::fake();

    box('Hello World.');

    Prompt::assertStrippedOutputContains(<<<'OUTPUT'
         ┌──────────────────────────────────────────────────────────────┐
         │ Hello World.                                                 │
         └──────────────────────────────────────────────────────────────┘
        OUTPUT);
});

it('renders a full box', function () {
    Prompt::fake();

    box(
        message: "Route cache .......... cleared\nConfig cache ......... cleared\nView cache ........... cleared",
        title: 'Cache Status',
        footer: 'Completed in 0.42s',
        color: 'green',
        info: 'v1.2',
    );

    Prompt::assertStrippedOutputContains(<<<'OUTPUT'
         ┌ Cache Status ────────────────────────────────────────────────┐
         │ Route cache .......... cleared                               │
         │ Config cache ......... cleared                               │
         │ View cache ........... cleared                               │
         ├──────────────────────────────────────────────────────────────┤
         │ Completed in 0.42s                                           │
         └──────────────────────────────────────────────────────── v1.2 ┘
        OUTPUT);
});
