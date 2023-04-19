<?php

use Laravel\Prompts\Key;
use function Laravel\Prompts\multiselect;
use Laravel\Prompts\MultiSelectPrompt;
use Laravel\Prompts\Prompt;

it('accepts an array of labels', function () {
    Prompt::fake([Key::DOWN, Key::SPACE, Key::DOWN, Key::SPACE, Key::ENTER]);

    $result = multiselect(
        label: 'What are your favorite colors?',
        options: [
            'Red',
            'Green',
            'Blue',
        ]
    );

    expect($result)->toBe(['Green', 'Blue']);
});

it('accepts an array of keys and labels', function () {
    Prompt::fake([Key::DOWN, Key::SPACE, Key::DOWN, Key::SPACE, Key::ENTER]);

    $result = multiselect(
        label: 'What are your favorite colors?',
        options: [
            'red' => 'Red',
            'green' => 'Green',
            'blue' => 'Blue',
        ]
    );

    expect($result)->toBe(['green', 'blue']);
});

it('accepts default values when the options are labels', function () {
    Prompt::fake([Key::ENTER]);

    $result = multiselect(
        label: 'What are your favorite colors?',
        options: [
            'Red',
            'Green',
            'Blue',
        ],
        default: ['Green']
    );

    expect($result)->toBe(['Green']);
});

it('accepts default values when the options are keys with labels', function () {
    Prompt::fake([Key::ENTER]);

    $result = multiselect(
        label: 'What are your favorite colors?',
        options: [
            'red' => 'Red',
            'green' => 'Green',
            'blue' => 'Blue',
        ],
        default: ['green']
    );

    expect($result)->toBe(['green']);
});

it('validates', function () {
    Prompt::fake([Key::ENTER, Key::SPACE, Key::ENTER]);

    $result = multiselect(
        label: 'What are your favorite colors?',
        options: [
            'red' => 'Red',
            'green' => 'Green',
            'blue' => 'Blue',
        ],
        validate: fn ($values) => count($values) === 0 ? 'You must select at least one color.' : null
    );

    expect($result)->toBe(['red']);

    Prompt::assertOutputContains('You must select at least one color.');
});

it('can fall back', function () {
    Prompt::fallbackWhen(true);

    MultiSelectPrompt::fallbackUsing(function (MultiSelectPrompt $prompt) {
        expect($prompt->label)->toBe('What is your favorite color?');

        return ['Blue'];
    });

    $result = multiselect('What is your favorite color?', [
        'Red',
        'Green',
        'Blue',
    ]);

    expect($result)->toBe(['Blue']);
});
