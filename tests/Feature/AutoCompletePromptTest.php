<?php

use Laravel\Prompts\AutoCompletePrompt;
use Laravel\Prompts\Exceptions\NonInteractiveValidationException;
use Laravel\Prompts\Key;
use Laravel\Prompts\Prompt;

use function Laravel\Prompts\autocomplete;

it('accepts any input', function () {
    Prompt::fake(['B', 'l', 'a', 'c', 'k', Key::ENTER]);

    $result = autocomplete('What is your favorite color?', [
        'Red',
        'Green',
        'Blue',
    ]);

    expect($result)->toBe('Black');
});

it('completes the input using the tab key', function () {
    Prompt::fake(['B', 'l', Key::TAB, Key::ENTER]);

    $result = autocomplete('What is your favorite color?', [
        'Red',
        'Green',
        'Blue',
    ]);

    expect($result)->toBe('Blue');
});

it('completes the input using the right arrow key', function () {
    Prompt::fake(['B', 'l', Key::RIGHT_ARROW, Key::ENTER]);

    $result = autocomplete('What is your favorite color?', [
        'Red',
        'Green',
        'Blue',
    ]);

    expect($result)->toBe('Blue');
});

it('cycles through suggestions with arrow keys', function () {
    Prompt::fake(['B', Key::DOWN, Key::TAB, Key::ENTER]);

    $result = autocomplete('What is your favorite color?', [
        'Red',
        'Blue',
        'Black',
    ]);

    expect($result)->toBe('Black');
});

it('cycles through suggestions wrapping around', function () {
    Prompt::fake(['B', Key::UP, Key::TAB, Key::ENTER]);

    $result = autocomplete('What is your favorite color?', [
        'Red',
        'Blue',
        'Black',
    ]);

    // UP from 0 wraps to last match (Black)
    expect($result)->toBe('Black');
});

it('allows editing after accepting a suggestion', function () {
    Prompt::fake(['B', 'l', Key::TAB, Key::BACKSPACE, Key::BACKSPACE, 'a', 'c', 'k', Key::ENTER]);

    $result = autocomplete('What is your favorite color?', [
        'Red',
        'Green',
        'Blue',
        'Black',
    ]);

    expect($result)->toBe('Black');
});

it('accepts a closure for options', function () {
    Prompt::fake(['a', 'p', 'p', '/', Key::TAB, Key::ENTER]);

    $result = autocomplete(
        label: 'Which file?',
        options: fn (string $value) => array_values(array_filter(
            ['app/Models/User.php', 'config/app.php'],
            fn ($file) => str_starts_with(strtolower($file), strtolower($value)),
        )),
    );

    expect($result)->toBe('app/Models/User.php');
});

it('resets highlighted index when typing', function () {
    Prompt::fake(['B', Key::DOWN, 'l', Key::TAB, Key::ENTER]);

    $result = autocomplete('What is your favorite color?', [
        'Red',
        'Blue',
        'Black',
        'Blurple',
    ]);

    // After DOWN, highlighted is on Black. Typing 'l' resets to 0, so TAB picks Blue.
    expect($result)->toBe('Blue');
});

it('tab requests suggestions when no ghost text is showing', function () {
    Prompt::fake(['B', 'l', 'u', 'e', Key::TAB, Key::ENTER]);

    $result = autocomplete('What is your favorite color?', [
        'Blue',
    ]);

    // Typed "Blue" exactly matches the option, no ghost text. TAB refreshes (no-op), enter submits.
    expect($result)->toBe('Blue');
});

it('transforms values', function () {
    Prompt::fake(['B', 'l', Key::TAB, Key::ENTER]);

    $result = autocomplete(
        label: 'What is your favorite color?',
        options: ['Blue'],
        transform: fn ($value) => strtoupper($value),
    );

    expect($result)->toBe('BLUE');
});

it('validates', function () {
    Prompt::fake([Key::ENTER, 'X', Key::ENTER]);

    $result = autocomplete(
        label: 'What is your name?',
        options: ['Taylor'],
        validate: fn ($value) => empty($value) ? 'Please enter your name.' : null,
    );

    expect($result)->toBe('X');

    Prompt::assertOutputContains('Please enter your name.');
});

it('can fall back', function () {
    Prompt::fallbackWhen(true);

    AutoCompletePrompt::fallbackUsing(function (AutoCompletePrompt $prompt) {
        expect($prompt->label)->toBe('What is your favorite color?');

        return 'result';
    });

    $result = autocomplete('What is your favorite color?', [
        'Red',
        'Green',
        'Blue',
    ]);

    expect($result)->toBe('result');
});

it('returns an empty string when non-interactive', function () {
    Prompt::interactive(false);

    $result = autocomplete('What is your favorite color?', [
        'Red',
        'Green',
        'Blue',
    ]);

    expect($result)->toBe('');
});

it('returns the default value when non-interactive', function () {
    Prompt::interactive(false);

    $result = autocomplete('What is your favorite color?', [
        'Red',
        'Green',
        'Blue',
    ], default: 'Yellow');

    expect($result)->toBe('Yellow');
});

it('validates the default value when non-interactive', function () {
    Prompt::interactive(false);

    autocomplete('What is your favorite color?', [
        'Red',
        'Green',
        'Blue',
    ], required: true);
})->throws(NonInteractiveValidationException::class, 'Required.');

it('accepts a collection', function () {
    Prompt::fake(['B', 'l', Key::TAB, Key::ENTER]);

    $result = autocomplete('What is your favorite color?', collect([
        'Red',
        'Green',
        'Blue',
    ]));

    expect($result)->toBe('Blue');
})->skip(! depends_on_collection());

it('supports custom validation', function () {
    Prompt::validateUsing(function (Prompt $prompt) {
        expect($prompt)
            ->label->toBe('What is your name?')
            ->validate->toBe('min:2');

        return $prompt->validate === 'min:2' && strlen($prompt->value()) < 2 ? 'Minimum 2 chars!' : null;
    });

    Prompt::fake(['A', Key::ENTER, 'n', 'd', 'r', 'e', 'a', Key::ENTER]);

    $result = autocomplete(
        label: 'What is your name?',
        options: ['Jess', 'Taylor'],
        validate: 'min:2',
    );

    expect($result)->toBe('Andrea');

    Prompt::assertOutputContains('Minimum 2 chars!');

    Prompt::validateUsing(fn () => null);
});
