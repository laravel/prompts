<?php

use Laravel\Prompts\Key;
use Laravel\Prompts\MultiSearchPrompt;
use Laravel\Prompts\Prompt;

use function Laravel\Prompts\multisearch;

it('supports default results', function ($options, $expected) {
    Prompt::fake([
        Key::UP, // Highlight "Violet"
        Key::SPACE, // Select "Violet"
        'G', // Search for "Green"
        'r', // Search for "Green"
        Key::DOWN, // Highlight "Green"
        Key::SPACE, // Select "Green"
        Key::BACKSPACE, // Clear search
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
         │   ◻ Red                                                    ┃ │
         │   ◻ Orange                                                 │ │
         │   ◻ Yellow                                                 │ │
         │   ◻ Green                                                  │ │
         │   ◻ Blue                                                   │ │
         └────────────────────────────────────────────────── 0 selected ┘
        OUTPUT);

    Prompt::assertStrippedOutputContains(<<<'OUTPUT'
         ┌ What are your favorite colors? ──────────────────────────────┐
         │ Search...                                                    │
         ├──────────────────────────────────────────────────────────────┤
         │   ◻ Yellow                                                 │ │
         │   ◻ Green                                                  │ │
         │   ◻ Blue                                                   │ │
         │   ◻ Indigo                                                 │ │
         │ › ◻ Violet                                                 ┃ │
         └────────────────────────────────────────────────── 0 selected ┘
        OUTPUT);

    Prompt::assertStrippedOutputContains(<<<'OUTPUT'
         ┌ What are your favorite colors? ──────────────────────────────┐
         │ Search...                                                    │
         ├──────────────────────────────────────────────────────────────┤
         │   ◻ Yellow                                                 │ │
         │   ◻ Green                                                  │ │
         │   ◻ Blue                                                   │ │
         │   ◻ Indigo                                                 │ │
         │ › ◼ Violet                                                 ┃ │
         └────────────────────────────────────────────────── 1 selected ┘
        OUTPUT);

    Prompt::assertStrippedOutputContains(<<<'OUTPUT'
         ┌ What are your favorite colors? ──────────────────────────────┐
         │ Gr                                                           │
         ├──────────────────────────────────────────────────────────────┤
         │   ◻ Green                                                    │
         └─────────────────────────────────────── 1 selected (1 hidden) ┘
        OUTPUT);

    Prompt::assertStrippedOutputContains(<<<'OUTPUT'
         ┌ What are your favorite colors? ──────────────────────────────┐
         │ Gr                                                           │
         ├──────────────────────────────────────────────────────────────┤
         │ › ◻ Green                                                    │
         └─────────────────────────────────────── 1 selected (1 hidden) ┘
        OUTPUT);

    Prompt::assertStrippedOutputContains(<<<'OUTPUT'
         ┌ What are your favorite colors? ──────────────────────────────┐
         │ Gr                                                           │
         ├──────────────────────────────────────────────────────────────┤
         │ › ◼ Green                                                    │
         └─────────────────────────────────────── 2 selected (1 hidden) ┘
        OUTPUT);

    Prompt::assertStrippedOutputContains(<<<'OUTPUT'
         ┌ What are your favorite colors? ──────────────────────────────┐
         │ Search...                                                    │
         ├──────────────────────────────────────────────────────────────┤
         │   ◻ Red                                                    ┃ │
         │   ◻ Orange                                                 │ │
         │   ◻ Yellow                                                 │ │
         │   ◼ Green                                                  │ │
         │   ◻ Blue                                                   │ │
         └────────────────────────────────────────────────── 2 selected ┘
        OUTPUT);

    Prompt::assertStrippedOutputContains(<<<'OUTPUT'
         ┌ What are your favorite colors? ──────────────────────────────┐
         │ Violet                                                       │
         │ Green                                                        │
         └──────────────────────────────────────────────────────────────┘
        OUTPUT);

    expect($result)->toBe($expected);
})->with([
    'associative' => [
        fn ($value) => collect([
            'red' => 'Red',
            'orange' => 'Orange',
            'yellow' => 'Yellow',
            'green' => 'Green',
            'blue' => 'Blue',
            'indigo' => 'Indigo',
            'violet' => 'Violet',
        ])->when(
            strlen($value),
            fn ($colors) => $colors->filter(fn ($label) => str_contains(strtolower($label), strtolower($value)))
        )->all(),
        ['violet', 'green'],
    ],
    'list' => [
        fn ($value) => collect(['Red', 'Orange', 'Yellow', 'Green', 'Blue', 'Indigo', 'Violet'])->when(
            strlen($value),
            fn ($colors) => $colors->filter(fn ($label) => str_contains(strtolower($label), strtolower($value)))
        )->values()->all(),
        ['Violet', 'Green'],
    ],
]);

it('supports no default results', function ($options, $expected) {
    Prompt::fake([
        'V', // Search for "Violet"
        Key::UP, // Highlight "Violet"
        Key::SPACE, // Select "Violet"
        Key::BACKSPACE, // Clear search
        'G', // Search for "Green"
        'r', // Search for "Green"
        Key::DOWN, // Highlight "Green"
        Key::SPACE, // Select "Green"
        Key::BACKSPACE, // Clear search
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
         ┌ What are your favorite colors? ──────────────────────────────┐
         │ V                                                            │
         ├──────────────────────────────────────────────────────────────┤
         │   ◻ Violet                                                   │
         └────────────────────────────────────────────────── 0 selected ┘
        OUTPUT);

    Prompt::assertStrippedOutputContains(<<<'OUTPUT'
         ┌ What are your favorite colors? ──────────────────────────────┐
         │ V                                                            │
         ├──────────────────────────────────────────────────────────────┤
         │ › ◻ Violet                                                   │
         └────────────────────────────────────────────────── 0 selected ┘
        OUTPUT);

    Prompt::assertStrippedOutputContains(<<<'OUTPUT'
         ┌ What are your favorite colors? ──────────────────────────────┐
         │ V                                                            │
         ├──────────────────────────────────────────────────────────────┤
         │ › ◼ Violet                                                   │
         └────────────────────────────────────────────────── 1 selected ┘
        OUTPUT);

    Prompt::assertStrippedOutputContains(<<<'OUTPUT'
         ┌ What are your favorite colors? ──────────────────────────────┐
         │ Search...                                                    │
         ├──────────────────────────────────────────────────────────────┤
         │   ◼ Violet                                                   │
         └────────────────────────────────────────────────── 1 selected ┘
        OUTPUT);

    Prompt::assertStrippedOutputContains(<<<'OUTPUT'
         ┌ What are your favorite colors? ──────────────────────────────┐
         │ Gr                                                           │
         ├──────────────────────────────────────────────────────────────┤
         │   ◻ Green                                                    │
         └─────────────────────────────────────── 1 selected (1 hidden) ┘
        OUTPUT);

    Prompt::assertStrippedOutputContains(<<<'OUTPUT'
         ┌ What are your favorite colors? ──────────────────────────────┐
         │ Gr                                                           │
         ├──────────────────────────────────────────────────────────────┤
         │ › ◻ Green                                                    │
         └─────────────────────────────────────── 1 selected (1 hidden) ┘
        OUTPUT);

    Prompt::assertStrippedOutputContains(<<<'OUTPUT'
         ┌ What are your favorite colors? ──────────────────────────────┐
         │ Gr                                                           │
         ├──────────────────────────────────────────────────────────────┤
         │ › ◼ Green                                                    │
         └─────────────────────────────────────── 2 selected (1 hidden) ┘
        OUTPUT);

    Prompt::assertStrippedOutputContains(<<<'OUTPUT'
         ┌ What are your favorite colors? ──────────────────────────────┐
         │ Search...                                                    │
         ├──────────────────────────────────────────────────────────────┤
         │   ◼ Violet                                                   │
         │   ◼ Green                                                    │
         └────────────────────────────────────────────────── 2 selected ┘
        OUTPUT);

    Prompt::assertStrippedOutputContains(<<<'OUTPUT'
         ┌ What are your favorite colors? ──────────────────────────────┐
         │ Violet                                                       │
         │ Green                                                        │
         └──────────────────────────────────────────────────────────────┘
        OUTPUT);

    expect($result)->toBe($expected);
})->with([
    'associative' => [
        fn ($value) => strlen($value) > 0 ? collect([
            'red' => 'Red',
            'orange' => 'Orange',
            'yellow' => 'Yellow',
            'green' => 'Green',
            'blue' => 'Blue',
            'indigo' => 'Indigo',
            'violet' => 'Violet',
        ])->filter(fn ($label) => str_contains(strtolower($label), strtolower($value)))->all() : [],
        ['violet', 'green'],
    ],
    'list' => [
        fn ($value) => strlen($value) > 0 ? collect(['Red', 'Orange', 'Yellow', 'Green', 'Blue', 'Indigo', 'Violet'])
            ->filter(fn ($label) => str_contains(strtolower($label), strtolower($value)))
            ->values()
            ->all() : [],
        ['Violet', 'Green'],
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

it('supports the home and end keys while navigating options', function () {
    Prompt::fake([Key::DOWN, Key::END[0], Key::SPACE, Key::HOME[0], Key::SPACE, Key::ENTER]);

    $result = multisearch(
        label: 'What are your favorite colors?',
        options: fn () => [
            'red' => 'Red',
            'green' => 'Green',
            'blue' => 'Blue',
        ]
    );

    expect($result)->toBe(['blue', 'red']);
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

it('supports custom validation', function () {
    Prompt::fake(['a', Key::DOWN, Key::SPACE, Key::ENTER, Key::DOWN, Key::SPACE, Key::ENTER]);

    Prompt::validateUsing(function (Prompt $prompt) {
        expect($prompt)
            ->label->toBe('What are your favorite colors?')
            ->validate->toBe('in:green');

        return $prompt->validate === 'in:green' && ! in_array('green', $prompt->value()) ? 'And green?' : null;
    });

    $result = multisearch(
        label: 'What are your favorite colors?',
        options: fn () => [
            'red' => 'Red',
            'green' => 'Green',
            'blue' => 'Blue',
        ],
        validate: 'in:green',
    );

    expect($result)->toBe(['red', 'green']);

    Prompt::assertOutputContains('And green?');

    Prompt::validateUsing(fn () => null);
});

it('supports selecting all options', function () {
    Prompt::fake([Key::DOWN, Key::CTRL_A, Key::ENTER]);

    $result = multisearch(
        label: 'What are your favorite colors?',
        options: fn () => [
            'red' => 'Red',
            'green' => 'Green',
            'blue' => 'Blue',
        ],
    );

    expect($result)->toBe(['red', 'green', 'blue']);

    Prompt::fake([Key::DOWN, Key::CTRL_A, Key::CTRL_A, Key::ENTER]);

    $result = multisearch(
        label: 'What are your favorite colors?',
        options: fn () => [
            'red' => 'Red',
            'green' => 'Green',
            'blue' => 'Blue',
        ],
    );

    expect($result)->toBe([]);
});
