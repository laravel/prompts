<?php

use Laravel\Prompts\Key;
use Laravel\Prompts\MultiSearchPrompt;
use Laravel\Prompts\Prompt;

use function Laravel\Prompts\multisearch;

it('supports default results', function ($options, $expected) {
    Prompt::fake([
        Key::DOWN, // Highlight "Red"
        Key::DOWN, // Highlight "Green"
        Key::SPACE, // Select "Green"
        'B', // Search for "Blue"
        Key::DOWN, // Highlight "Blue"
        Key::SPACE, // Select "Blue"
        Key::BACKSPACE, // Clear search
        Key::ENTER, // Confirm selection
    ]);

    $result = multisearch(
        label: 'What are your favorite colors?',
        placeholder: 'Search...',
        options: $options,
    );

    Prompt::assertStrippedOutputContains(<<<'OUTPUT'
         ┌ What are your favorite colors? ──────────────────────────────┐
         │ Search...                                                    │
         ├──────────────────────────────────────────────────────────────┤
         │   ◻ Red                                                      │
         │   ◻ Green                                                    │
         │   ◻ Blue                                                     │
         └────────────────────────────────────────────────── 0 selected ┘
        OUTPUT);

    Prompt::assertStrippedOutputContains(<<<'OUTPUT'
         │ Search...                                                    │
         ├──────────────────────────────────────────────────────────────┤
         │   ◻ Red                                                      │
         │   ◼ Green                                                    │
         │   ◼ Blue                                                     │
         └────────────────────────────────────────────────── 2 selected ┘
        OUTPUT);

    Prompt::assertStrippedOutputContains(<<<'OUTPUT'
         ┌ What are your favorite colors? ──────────────────────────────┐
         │ Green                                                        │
         │ Blue                                                         │
         └──────────────────────────────────────────────────────────────┘
        OUTPUT);

    expect($result)->toBe($expected);
})->with([
    'associative' => [
        fn ($value) => collect([
            'red' => 'Red',
            'green' => 'Green',
            'blue' => 'Blue',
        ])->when(
            strlen($value),
            fn ($colors) => $colors->filter(fn ($label) => str_contains(strtolower($label), strtolower($value)))
        )->all(),
        ['green', 'blue'],
    ],
    'list' => [
        fn ($value) => collect(['Red', 'Green', 'Blue'])->when(
            strlen($value),
            fn ($colors) => $colors->filter(fn ($label) => str_contains(strtolower($label), strtolower($value)))
        )->values()->all(),
        ['Green', 'Blue'],
    ],
]);

it('supports no default results', function ($options, $expected) {
    Prompt::fake([
        'B', // Search for "Blue"
        Key::DOWN, // Highlight "Blue"
        Key::SPACE, // Select "Blue"
        Key::BACKSPACE, // Clear search
        'G', // Search for "Green"
        Key::DOWN, // Highlight "Green"
        Key::SPACE, // Select "Green"
        Key::BACKSPACE, // Clear search
        Key::ENTER, // Confirm selection
    ]);

    $result = multisearch(
        label: 'What are your favorite colors?',
        placeholder: 'Search...',
        options: $options,
    );

    Prompt::assertStrippedOutputContains(<<<'OUTPUT'
         ┌ What are your favorite colors? ──────────────────────────────┐
         │ Search...                                                    │
         └────────────────────────────────────────────────── 0 selected ┘
        OUTPUT);

    Prompt::assertStrippedOutputContains(<<<'OUTPUT'
         │ Search...                                                    │
         ├──────────────────────────────────────────────────────────────┤
         │   ◼ Blue                                                     │
         │   ◼ Green                                                    │
         └────────────────────────────────────────────────── 2 selected ┘
        OUTPUT);

    Prompt::assertStrippedOutputContains(<<<'OUTPUT'
         ┌ What are your favorite colors? ──────────────────────────────┐
         │ Blue                                                         │
         │ Green                                                        │
         └──────────────────────────────────────────────────────────────┘
        OUTPUT);

    expect($result)->toBe($expected);
})->with([
    'associative' => [
        fn ($value) => strlen($value) > 0 ? collect([
            'red' => 'Red',
            'green' => 'Green',
            'blue' => 'Blue',
        ])->filter(fn ($label) => str_contains(strtolower($label), strtolower($value)))->all() : [],
        ['blue', 'green'],
    ],
    'list' => [
        fn ($value) => strlen($value) > 0 ? collect(['Red', 'Green', 'Blue'])
            ->filter(fn ($label) => str_contains(strtolower($label), strtolower($value)))
            ->values()
            ->all() : [],
        ['Blue', 'Green'],
    ],
]);

it('validates', function () {
    Prompt::fake(['a', Key::DOWN, Key::SPACE, Key::ENTER, Key::DOWN, Key::SPACE, Key::ENTER]);

    $result = multisearch(
        label: 'What are your favorite colors?',
        options: fn () => [
            'red' => 'Red',
            'green' => 'Green',
            'blue' => 'Blue',
        ],
        validate: fn ($value) => ! in_array('green', $value) ? 'Please choose green.' : null
    );

    expect($result)->toBe(['red', 'green']);

    Prompt::assertOutputContains('Please choose green.');
});

it('can fall back', function () {
    Prompt::fallbackWhen(true);

    MultiSearchPrompt::fallbackUsing(function (MultiSearchPrompt $prompt) {
        expect($prompt->label)->toBe('What are your favorite colors?');

        return ['result'];
    });

    $result = multisearch(
        label: 'What are your favorite colors?',
        options: fn () => [
            'red' => 'Red',
            'green' => 'Green',
            'blue' => 'Blue',
        ],
    );

    expect($result)->toBe(['result']);
});
