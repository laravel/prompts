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

it('supports scrolled results', function ($options, $expected) {
    Prompt::fake([
        'B', // Search for "Blue"
        Key::UP, // Highlight "Blue"
        Key::SPACE, // Select "Blue"
        Key::UP, // Highlight "Blue"
        Key::UP, // Highlight "Blue"
        Key::SPACE, // Select "Blue"
        Key::BACKSPACE, // Clear search
        'G', // Search for "Green"
        Key::UP, // Highlight "Green"
        Key::SPACE, // Select "Green"
        Key::UP, // Highlight "Green"
        Key::UP, // Highlight "Green"
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
         │   ◼ Blue-900                                                 │
         │   ◼ Blue-700                                                 │
         │   ◼ Green-700                                                │
         │   ◼ Green-500                                                │
         └────────────────────────────────────────────────── 4 selected ┘
        OUTPUT);

    Prompt::assertStrippedOutputContains(<<<'OUTPUT'
         ┌ What are your favorite colors? ──────────────────────────────┐
         │ Blue-900                                                     │
         │ Blue-700                                                     │
         │ Green-700                                                    │
         │ Green-500                                                    │
         └──────────────────────────────────────────────────────────────┘
        OUTPUT);

    expect($result)->toBe($expected);
})->with([
    'associative' => [
        fn ($value) => strlen($value) > 0 ? collect([
            'red' => 'Red',
            'green-100' => 'Green-100',
            'green-200' => 'Green-200',
            'green-300' => 'Green-300',
            'green-400' => 'Green-400',
            'green-500' => 'Green-500',
            'green-600' => 'Green-600',
            'green-700' => 'Green-700',
            'blue-100' => 'Blue-100',
            'blue-200' => 'Blue-200',
            'blue-300' => 'Blue-300',
            'blue-400' => 'Blue-400',
            'blue-500' => 'Blue-500',
            'blue-600' => 'Blue-600',
            'blue-700' => 'Blue-700',
            'blue-800' => 'Blue-800',
            'blue-900' => 'Blue-900',
        ])->filter(fn ($label) => str_contains(strtolower($label), strtolower($value)))->all() : [],
        ['blue-900', 'blue-700', 'green-700', 'green-500'],
    ],
    'list' => [
        fn ($value) => strlen($value) > 0 ? collect([
            'Red',
            'Green-100',
            'Green-200',
            'Green-300',
            'Green-400',
            'Green-500',
            'Green-600',
            'Green-700',
            'Blue-100',
            'Blue-200',
            'Blue-300',
            'Blue-400',
            'Blue-500',
            'Blue-600',
            'Blue-700',
            'Blue-800',
            'Blue-900',
        ])->filter(fn ($label) => str_contains(strtolower($label), strtolower($value)))
        ->values()
        ->all() : [],
        ['Blue-900', 'Blue-700', 'Green-700', 'Green-500'],
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
