<?php

use Laravel\Prompts\Key;
use Laravel\Prompts\Prompt;
use function Laravel\Prompts\multiselect;

it('accepts an array of labels', function () {
    Prompt::fake([Key::DOWN, Key::SPACE, Key::DOWN, Key::SPACE, Key::ENTER]);

    $result = multiselect(
        message: 'What are your favorite colors?',
        options: [
            'Red',
            'Green',
            'Blue'
        ]
    );

    expect($result)->toBe(['Green', 'Blue']);
});

it('accepts an array of keys and labels', function () {
    Prompt::fake([Key::DOWN, Key::SPACE, Key::DOWN, Key::SPACE, Key::ENTER]);

    $result = multiselect(
        message: 'What are your favorite colors?',
        options: [
            'red' => 'Red',
            'green' => 'Green',
            'blue' => 'Blue'
        ]
    );

    expect($result)->toBe(['green', 'blue']);
});

it('accepts default values when the options are labels', function () {
    Prompt::fake([Key::ENTER]);

    $result = multiselect(
        message: 'What are your favorite colors?',
        options: [
            'Red',
            'Green',
            'Blue'
        ],
        default: ['Green']
    );

    expect($result)->toBe(['Green']);
});

it('accepts default values when the options are keys with labels', function () {
    Prompt::fake([Key::ENTER]);

    $result = multiselect(
        message: 'What are your favorite colors?',
        options: [
            'red' => 'Red',
            'green' => 'Green',
            'blue' => 'Blue'
        ],
        default: ['green']
    );

    expect($result)->toBe(['green']);
});
