<?php

use Laravel\Prompts\Key;
use Laravel\Prompts\Prompt;
use Laravel\Prompts\SearchPrompt;

use function Laravel\Prompts\search;

it('accepts a callback', function () {
    Prompt::fake(['u', 'e', Key::DOWN, Key::ENTER]);

    $result = search(
        label: 'What is your favorite color?',
        options: fn (string $value) => array_filter(
            [
                'red' => 'Red',
                'green' => 'Green',
                'blue' => 'Blue',
            ],
            fn ($option) => str_contains(strtolower($option), strtolower($value)),
        ),
    );

    expect($result)->toBe('blue');
});

it('returns the value when a list is passed', function () {
    Prompt::fake(['u', 'e', Key::DOWN, Key::ENTER]);

    $result = search(
        label: 'What is your favorite color?',
        options: fn (string $value) => array_values(array_filter(
            [
                'Red',
                'Green',
                'Blue',
            ],
            fn ($option) => str_contains(strtolower($option), strtolower($value)),
        )),
    );

    expect($result)->toBe('Blue');
});

it('returns the key when an associative array is passed', function () {
    Prompt::fake(['u', 'e', Key::DOWN, Key::ENTER]);

    $result = search(
        label: 'What is your favorite color?',
        options: fn (string $value) => array_filter(
            [
                1 => 'Red',
                2 => 'Green',
                3 => 'Blue',
            ],
            fn ($option) => str_contains(strtolower($option), strtolower($value)),
        ),
    );

    expect($result)->toBe(3);
});

it('validates', function () {
    Prompt::fake([Key::DOWN, Key::ENTER, Key::DOWN, Key::ENTER]);

    $result = search(
        label: 'What is your favorite color?',
        options: fn () => [
            'red' => 'Red',
            'green' => 'Green',
            'blue' => 'Blue',
        ],
        validate: fn ($value) => $value === 'red' ? 'Please choose green.' : null
    );

    expect($result)->toBe('green');

    Prompt::assertOutputContains('Please choose green.');
});

it('can fall back', function () {
    Prompt::fallbackWhen(true);

    SearchPrompt::fallbackUsing(function (SearchPrompt $prompt) {
        expect($prompt->label)->toBe('What is your favorite color?');

        return 'result';
    });

    $result = search(
        label: 'What is your favorite color?',
        options: fn () => [
            'red' => 'Red',
            'green' => 'Green',
            'blue' => 'Blue',
        ],
    );

    expect($result)->toBe('result');
});
