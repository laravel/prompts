<?php

use Laravel\Prompts\Key;
use Laravel\Prompts\MultiSearchPrompt;
use Laravel\Prompts\Prompt;

use function Laravel\Prompts\multisearch;

it('allows unfiltered results', function () {
    Prompt::fake([Key::DOWN, Key::DOWN, Key::SPACE, Key::DOWN, Key::SPACE, Key::ENTER]);

    $result = multisearch(
        label: 'What are your favorite colors?',
        placeholder: 'Search...',
        options: fn ($value) => collect([
            'red' => 'Red',
            'green' => 'Green',
            'blue' => 'Blue',
        ])->when(
            strlen($value),
            fn ($vendor) => $vendor->filter(fn ($label) => str_contains(strtolower($label), strtolower($value)))
        )->all(),
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
         ┌ What are your favorite colors? ──────────────────────────────┐
         │ Green                                                        │
         │ Blue                                                         │
         └──────────────────────────────────────────────────────────────┘
        OUTPUT);

    expect($result)->toBe(['green', 'blue']);
});

it("maintains selections when the search value and results are empty", function () {
    Prompt::fake([
        'u', 'e', Key::DOWN, Key::SPACE, // Select Blue
        Key::BACKSPACE, Key::BACKSPACE, // Clear search
        'e', 'n', Key::DOWN, Key::SPACE, // Select Green
        Key::BACKSPACE, Key::BACKSPACE, // Clear search
        Key::ENTER, // Confirm selection
    ]);

    $result = multisearch(
        label: 'What are your favorite colors?',
        placeholder: 'Search...',
        options: function ($value) {
            if (strlen($value) === 0) {
                return [];
            }

            return collect([
                'red' => 'Red',
                'green' => 'Green',
                'blue' => 'Blue',
            ])->filter(fn ($label) => str_contains(strtolower($label), strtolower($value)))->all();
        },
    );

    Prompt::assertStrippedOutputContains(<<<OUTPUT
         ┌ What are your favorite colors? ──────────────────────────────┐
         │ Search...                                                    │
         └────────────────────────────────────────────────── 0 selected ┘
        OUTPUT);

    Prompt::assertStrippedOutputContains(<<<OUTPUT
         │ Search...                                                    │
         ├──────────────────────────────────────────────────────────────┤
         │   ◼ Blue                                                     │
         │   ◼ Green                                                    │
         └────────────────────────────────────────────────── 2 selected ┘
          Use the space bar to select options.
        OUTPUT);

    Prompt::assertStrippedOutputContains(<<<OUTPUT
         ┌ What are your favorite colors? ──────────────────────────────┐
         │ Blue                                                         │
         │ Green                                                        │
         └──────────────────────────────────────────────────────────────┘
        OUTPUT);

    expect($result)->toBe(['blue', 'green']);
});

it('returns the value when the options are a list', function () {
    Prompt::fake(['u', 'e', Key::DOWN, Key::SPACE, Key::ENTER]);

    $result = multisearch(
        label: 'What are your favorite colors?',
        options: fn ($value) => collect([
            'Red',
            'Green',
            'Blue',
        ])->when(
            strlen($value),
            fn ($vendor) => $vendor->filter(fn ($label) => str_contains(strtolower($label), strtolower($value)))
        )->values()->all(),
    );

    expect($result)->toBe(['Blue']);
});

it('validates', function () {
    Prompt::fake(['a', Key::DOWN, Key::SPACE, Key::ENTER, Key::DOWN, Key::SPACE, Key::ENTER]);

    $result = multisearch(
        label: 'What are your favorite colors?',
        options: fn () => [
            'red' => 'Red',
            'green' => 'Green',
            'blue' => 'Blue',
        ],
        validate: fn ($value) => !in_array('green', $value) ? 'Please choose green.' : null
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
