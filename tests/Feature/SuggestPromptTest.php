<?php

use Laravel\Prompts\Key;
use Laravel\Prompts\P;
use Laravel\Prompts\Prompt;
use Laravel\Prompts\SuggestPrompt;

it('accepts any input', function () {
    Prompt::fake(['B', 'l', 'a', 'c', 'k', Key::ENTER]);

    $result = P::suggest('What is your favorite color?', [
        'Red',
        'Green',
        'Blue',
    ]);

    expect($result)->toBe('Black');
});

it('completes the input using the tab key', function () {
    Prompt::fake(['b', Key::TAB, Key::ENTER]);

    $result = P::suggest('What is your favorite color?', [
        'Red',
        'Green',
        'Blue',
    ]);

    expect($result)->toBe('Blue');
});

it('completes the input using the arrow keys', function () {
    Prompt::fake(['b', Key::DOWN, Key::DOWN, Key::DOWN, Key::UP, Key::ENTER]);

    $result = P::suggest('What is your favorite color?', [
        'Red',
        'Blue',
        'Black',
        'Blurple',
    ]);

    expect($result)->toBe('Black');
});

it('accepts a callback', function () {
    Prompt::fake(['e', 'e', Key::DOWN, Key::ENTER]);

    $result = P::suggest(
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

it('accepts a collection', function () {
    Prompt::fake(['b', Key::TAB, Key::ENTER]);

    $result = P::suggest('What is your favorite color?', collect([
        'Red',
        'Green',
        'Blue',
    ]));

    expect($result)->toBe('Blue');
});

it('validates', function () {
    Prompt::fake([Key::ENTER, 'X', Key::ENTER]);

    $result = P::suggest(
        label: 'What is your name?',
        options: ['Taylor'],
        validate: fn ($value) => empty($value) ? 'Please enter your name.' : null,
    );

    expect($result)->toBe('X');

    Prompt::assertOutputContains('Please enter your name.');
});

it('can fall back', function () {
    Prompt::fallbackWhen(true);

    SuggestPrompt::fallbackUsing(function (SuggestPrompt $prompt) {
        expect($prompt->label)->toBe('What is your favorite color?');

        return 'result';
    });

    $result = P::suggest('What is your favorite color?', [
        'Red',
        'Green',
        'Blue',
    ]);

    expect($result)->toBe('result');
});
