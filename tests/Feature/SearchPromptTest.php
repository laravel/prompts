<?php

use Laravel\Prompts\Exceptions\NonInteractiveValidationException;
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

it('supports the home key while navigating options', function () {
    Prompt::fake(['r', Key::DOWN, Key::DOWN, Key::HOME[0], Key::ENTER]);

    $result = search(
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

    expect($result)->toBe('Red');
});

it('supports the end key while navigating options', function () {
    Prompt::fake(['r', Key::DOWN, Key::END[0], Key::ENTER]);

    $result = search(
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

it('transforms values', function () {
    Prompt::fake(['u', 'e', Key::DOWN, Key::ENTER]);

    $result = search(
        label: 'What is your favorite color?',
        options: fn (string $value) => array_filter(
            [
                'red' => 'Red',
                'green' => 'Green',
                'blue' => 'Blue',
            ],
            fn ($option) => str_contains(strtolower($option), $value),
        ),
        transform: fn ($value) => strtoupper($value),
    );

    expect($result)->toBe('BLUE');
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

it('support emacs style key binding', function () {
    Prompt::fake(['u', 'e', Key::CTRL_N, Key::ENTER]);

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

it('fails when when non-interactive', function () {
    Prompt::interactive(false);

    search('What is your favorite color?', fn () => []);
})->throws(NonInteractiveValidationException::class, 'Required.');

it('allows the required validation message to be customised when non-interactive', function () {
    Prompt::interactive(false);

    search('What is your favorite color?', fn () => [], required: 'The color is required.');
})->throws(NonInteractiveValidationException::class, 'The color is required.');

it('supports custom validation', function () {
    Prompt::fake([Key::DOWN, Key::ENTER, Key::DOWN, Key::ENTER]);

    Prompt::validateUsing(function (Prompt $prompt) {
        expect($prompt)
            ->label->toBe('What is your favorite color?')
            ->validate->toBe('in:green');

        return $prompt->validate === 'in:green' && $prompt->value() != 'green' ? 'Please choose green.' : null;
    });

    $result = search(
        label: 'What is your favorite color?',
        options: fn () => [
            'red' => 'Red',
            'green' => 'Green',
            'blue' => 'Blue',
        ],
        validate: 'in:green',
    );

    expect($result)->toBe('green');

    Prompt::assertOutputContains('Please choose green.');

    Prompt::validateUsing(fn () => null);
});
