<?php

use function Laravel\Prompts\anticipate;
use Laravel\Prompts\Key;
use Laravel\Prompts\Prompt;

it('accepts any input', function () {
    Prompt::fake(['B', 'l', 'a', 'c', 'k', Key::ENTER]);

    $result = anticipate('What is your favorite color?', [
        'Red',
        'Green',
        'Blue',
    ]);

    expect($result)->toBe('Black');
});

it('completes the input using the tab key', function () {
    Prompt::fake(['b', Key::TAB, Key::ENTER]);

    $result = anticipate('What is your favorite color?', [
        'Red',
        'Green',
        'Blue',
    ]);

    expect($result)->toBe('Blue');
});

it('completes the input using the arrow keys', function () {
    Prompt::fake(['b', Key::DOWN, Key::DOWN, Key::DOWN, Key::UP, Key::ENTER]);

    $result = anticipate('What is your favorite color?', [
        'Red',
        'Blue',
        'Black',
        'Blurple',
    ]);

    expect($result)->toBe('Black');
});

it('accepts a callback', function () {
    Prompt::fake(['e', 'e', Key::DOWN, Key::ENTER]);

    $result = anticipate(
        label: 'What is your favorite color?',
        options: fn (string $value) => array_filter(
            [
                'Red',
                'Green',
                'Blue',
            ],
            fn ($option) => str_contains(strtolower($option), strtolower($value)),
        ),
    );

    expect($result)->toBe('Green');
});
