<?php

use Laravel\Prompts\Exceptions\NonInteractiveValidationException;
use Laravel\Prompts\Key;
use Laravel\Prompts\Prompt;
use Laravel\Prompts\SuggestPrompt;

use function Laravel\Prompts\suggest;

it('accepts any input', function () {
    Prompt::fake(['B', 'l', 'a', 'c', 'k', Key::ENTER]);

    $result = suggest('What is your favorite color?', [
        'Red',
        'Green',
        'Blue',
    ]);

    expect($result)->toBe('Black');
});

it('completes the input using the tab key', function () {
    Prompt::fake(['b', Key::TAB, Key::ENTER]);

    $result = suggest('What is your favorite color?', [
        'Red',
        'Green',
        'Blue',
    ]);

    expect($result)->toBe('Blue');
});

it('completes the input using the arrow keys', function () {
    Prompt::fake(['b', Key::DOWN, Key::DOWN, Key::DOWN, Key::UP, Key::ENTER]);

    $result = suggest('What is your favorite color?', [
        'Red',
        'Blue',
        'Black',
        'Blurple',
    ]);

    expect($result)->toBe('Black');
});

it('supports the home key while navigating options', function () {
    Prompt::fake([Key::DOWN, Key::DOWN, Key::HOME[0], Key::ENTER]);

    $result = suggest('What is your favorite color?', [
        'Red',
        'Blue',
        'Green',
    ]);

    expect($result)->toBe('Red');
});

it('supports the end key while navigating options', function () {
    Prompt::fake([Key::DOWN, Key::END[0], Key::ENTER]);

    $result = suggest('What is your favorite color?', [
        'Red',
        'Blue',
        'Green',
    ]);

    expect($result)->toBe('Green');
});

it('accepts a callback', function () {
    Prompt::fake(['e', 'e', Key::DOWN, Key::ENTER]);

    $result = suggest(
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

    $result = suggest('What is your favorite color?', collect([
        'Red',
        'Green',
        'Blue',
    ]));

    expect($result)->toBe('Blue');
})->skip(! depends_on_collection());

it('accepts a callback returning a collection', function () {
    Prompt::fake(['b', Key::TAB, Key::ENTER]);

    $result = suggest(
        label: 'What is your favorite color?',
        options: fn ($value) => collect([
            'Red',
            'Green',
            'Blue',
        ])->filter(
            fn ($name) => str_contains(
                strtoupper($name),
                strtoupper($value)
            )
        )
    );

    expect($result)->toBe('Blue');
})->skip(! depends_on_collection());

it('transforms values', function () {
    Prompt::fake([Key::SPACE, 'J', 'e', 's', 's', Key::TAB, Key::ENTER]);

    $result = suggest(
        label: 'What is your name?',
        options: ['Jess'],
        transform: fn ($value) => trim($value),
    );

    expect($result)->toBe('Jess');
});

it('validates', function () {
    Prompt::fake([Key::ENTER, 'X', Key::ENTER]);

    $result = suggest(
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

    $result = suggest('What is your favorite color?', [
        'Red',
        'Green',
        'Blue',
    ]);

    expect($result)->toBe('result');
});

it('support emacs style key binding', function () {
    Prompt::fake(['b', Key::CTRL_N, Key::CTRL_N, Key::CTRL_N, Key::CTRL_P, Key::ENTER]);

    $result = suggest('What is your favorite color?', [
        'Red',
        'Blue',
        'Black',
        'Blurple',
    ]);

    expect($result)->toBe('Black');
});

it('returns an empty string when non-interactive', function () {
    Prompt::interactive(false);

    $result = suggest('What is your favorite color?', [
        'Red',
        'Green',
        'Blue',
    ]);

    expect($result)->toBe('');
});

it('returns the default value when non-interactive', function () {
    Prompt::interactive(false);

    $result = suggest('What is your favorite color?', [
        'Red',
        'Green',
        'Blue',
    ], default: 'Yellow');

    expect($result)->toBe('Yellow');
});

it('validates the default value when non-interactive', function () {
    Prompt::interactive(false);

    suggest('What is your favorite color?', [
        'Red',
        'Green',
        'Blue',
    ], required: true);
})->throws(NonInteractiveValidationException::class, 'Required.');

it('supports custom validation', function () {
    Prompt::validateUsing(function (Prompt $prompt) {
        expect($prompt)
            ->label->toBe('What is your name?')
            ->validate->toBe('min:2');

        return $prompt->validate === 'min:2' && strlen($prompt->value()) < 2 ? 'Minimum 2 chars!' : null;
    });

    Prompt::fake(['A', Key::ENTER, 'n', 'd', 'r', 'e', 'a', Key::ENTER]);

    $result = suggest(
        label: 'What is your name?',
        options: ['Jess', 'Taylor'],
        validate: 'min:2',
    );

    expect($result)->toBe('Andrea');

    Prompt::assertOutputContains('Minimum 2 chars!');

    Prompt::validateUsing(fn () => null);
});
