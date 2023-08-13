<?php

use Laravel\Prompts\Key;
use Laravel\Prompts\MultiSearchPrompt;
use Laravel\Prompts\Prompt;

use function Laravel\Prompts\multisearch;

it('accepts a callback', function () {
    Prompt::fake([
        'u', 'e', Key::DOWN, Key::SPACE, // Select Blue
        Key::BACKSPACE, Key::BACKSPACE, // Clear search
        'e', 'n', Key::DOWN, Key::SPACE, // Select Green
        Key::ENTER // Confirm selection
    ]);

    $result = multisearch(
        label: 'What are your favorite colors?',
        options: fn (string $value) => array_filter(
            [
                'red' => 'Red',
                'green' => 'Green',
                'blue' => 'Blue',
            ],
            fn ($option) => str_contains(strtolower($option), strtolower($value)),
        ),
    );

    expect($result)->toBe(['blue', 'green']);
    

    Prompt::assertStrippedOutputDoesntContain('│ Red');
    Prompt::assertStrippedOutputContains('│ Green');
    Prompt::assertStrippedOutputContains('│ Blue');
});

it('returns the value when return key flag is false', function () {
    Prompt::fake(['u', 'e', Key::DOWN, Key::SPACE, Key::ENTER]);

    $result = multisearch(
        label: 'What are your favorite colors?',
        options: fn (string $value) => array_values(array_filter(
            [
                'red' => 'Red',
                'green' => 'Green',
                'blue' => 'Blue',
            ],
            fn ($option) => str_contains(strtolower($option), strtolower($value)),
        )),
        returnKeys: false
    );

    expect($result)->toBe(['Blue']);
});

it('accepts default values when the options are keys with labels', function () {
    Prompt::fake([Key::ENTER]);

    $result = multisearch(
        label: 'What are your favorite colors?',
        options: fn () => [
            'red' => 'Red',
            'green' => 'Green',
            'blue' => 'Blue',
        ],
        default: [
            'green' => 'Green'
        ]
    );

    expect($result)->toBe(['green']);
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
