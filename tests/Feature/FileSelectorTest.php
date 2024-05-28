<?php

use Laravel\Prompts\FileSelector;
use Laravel\Prompts\Key;
use Laravel\Prompts\Exceptions\NonInteractiveValidationException;
use Laravel\Prompts\Prompt;

use function Laravel\Prompts\fileselector;

it('accepts any input', function () {
    Prompt::fake(['B', 'l', 'a', 'c', 'k', Key::ENTER]);

    $result = fileselector('What is your favorite color?');

    expect($result)->toBe('Black');
});

it('completes the input using the tab key', function () {
    Prompt::fake(['v', Key::DOWN, Key::TAB, Key::ENTER]);

    $result = fileselector('Select a file.');

    expect($result)->toBe('./vendor/');
});

it('completes the input using the arrow keys', function () {
    Prompt::fake(['.', 'g', Key::DOWN, Key::DOWN, Key::UP, Key::ENTER]);

    $result = fileselector('Select a file.');

    expect($result)->toBe('./.git/');
});

it('supports the home key while navigating options', function () {
    Prompt::fake([Key::DOWN, Key::DOWN, Key::DOWN, Key::HOME[0], Key::ENTER]);

    $result = fileselector('Select a file.');

    expect($result)->toBe('./.editorconfig');
});

it('supports the end key while navigating options', function () {
    Prompt::fake([Key::DOWN, Key::END[0], Key::ENTER]);

    $result = fileselector('What is your favorite color?');

    expect($result)->toBe('./vendor/');
});

it('validates', function () {
    Prompt::fake([Key::ENTER, 'X', Key::ENTER]);

    $result = fileselector(
        label: 'Select a file.',
        validate: fn ($value) => empty($value) ? 'Please select a file.' : null,
    );

    expect($result)->toBe('X');

    Prompt::assertOutputContains('Please select a file.');
});

it('can fall back', function () {
    Prompt::fallbackWhen(true);

    FileSelector::fallbackUsing(function (FileSelector $prompt) {
        expect($prompt->label)->toBe('Select a file.');

        return 'result';
    });

    $result = fileselector('Select a file.');

    expect($result)->toBe('result');
});

it('support emacs style key binding', function () {
    Prompt::fake(['.', 'g', Key::CTRL_N, Key::CTRL_N, Key::CTRL_N, Key::CTRL_P, Key::ENTER]);

    $result = fileselector('Select a file.');

    expect($result)->toBe('./.gitattributes');
});

it('returns an empty string when non-interactive', function () {
    Prompt::interactive(false);

    $result = fileselector('What is your favorite color?');

    expect($result)->toBe('');
});

it('returns the default value when non-interactive', function () {
    Prompt::interactive(false);

    $result = fileselector('What is your favorite color?', default: 'Yellow');

    expect($result)->toBe('Yellow');
});

it('validates the default value when non-interactive', function () {
    Prompt::interactive(false);

    fileselector('Select a file.', required: true);
})->throws(NonInteractiveValidationException::class, 'Required.');

it('supports custom validation', function () {
    Prompt::validateUsing(function (Prompt $prompt) {
        expect($prompt)
            ->label->toBe('Select a file.')
            ->validate->toBe('min:2');

        return $prompt->validate === 'min:2' && strlen($prompt->value()) < 2 ? 'Minimum 2 chars!' : null;
    });

    Prompt::fake(['A', Key::ENTER, 'n', 'd', 'r', 'e', 'a', Key::ENTER]);

    $result = fileselector(
        label: 'Select a file.',
        validate: 'min:2',
    );

    expect($result)->toBe('Andrea');

    Prompt::assertOutputContains('Minimum 2 chars!');

    Prompt::validateUsing(fn () => null);
});

it('update dir entries after auto complete', function () {
    Prompt::fake(['v', 'e', 'n', Key::UP, Key::TAB, 'a', 'u', Key::DOWN, Key::TAB, Key::ENTER]);

    $result = fileselector('Select a file.');

    expect($result)->toBe('./vendor/autoload.php');
});

it('filter dir entries with specified extensions', function () {
    Prompt::fake(['c', 'o', 'm', 'p', 'o', 's', 'e', 'r', Key::UP, Key::ENTER]);

    $result = fileselector(
        label: 'Select a file.',
        extensions: [
            '.json',
        ],
    );

    expect($result)->toBe('./composer.json');
});
