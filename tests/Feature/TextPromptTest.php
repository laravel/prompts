<?php

use Laravel\Prompts\Exceptions\NonInteractiveValidationException;
use Laravel\Prompts\Key;
use Laravel\Prompts\Prompt;
use Laravel\Prompts\TextPrompt;

use function Laravel\Prompts\text;

it('returns the input', function () {
    Prompt::fake(['J', 'e', 's', 's', Key::ENTER]);

    $result = text(label: 'What is your name?');

    expect($result)->toBe('Jess');
});

it('accepts a default value', function () {
    Prompt::fake([Key::ENTER]);

    $result = text(
        label: 'What is your name?',
        default: 'Jess'
    );

    expect($result)->toBe('Jess');
});

it('validates', function () {
    Prompt::fake(['J', 'e', 's', Key::ENTER, 's', Key::ENTER]);

    $result = text(
        label: 'What is your name?',
        validate: fn ($value) => $value !== 'Jess' ? 'Invalid name.' : '',
    );

    expect($result)->toBe('Jess');

    Prompt::assertOutputContains('Invalid name.');
});

it('cancels', function () {
    Prompt::fake([Key::CTRL_C]);

    text(label: 'What is your name?');

    Prompt::assertOutputContains('Cancelled.');
});

test('the backspace key removes a character', function () {
    Prompt::fake(['J', 'e', 'z', Key::BACKSPACE, 's', 's', Key::ENTER]);

    $result = text(label: 'What is your name?');

    expect($result)->toBe('Jess');
});

test('the delete key removes a character', function () {
    Prompt::fake(['J', 'e', 'z', Key::LEFT, Key::DELETE, 's', 's', Key::ENTER]);

    $result = text(label: 'What is your name?');

    expect($result)->toBe('Jess');
});

it('can fall back', function () {
    Prompt::fallbackWhen(true);

    TextPrompt::fallbackUsing(function (TextPrompt $prompt) {
        expect($prompt->label)->toBe('What is your name?');

        return 'result';
    });

    $result = text('What is your name?');

    expect($result)->toBe('result');
});

test('support emacs style key binding', function () {
    Prompt::fake(['J', 'z', 'e', Key::CTRL_B, Key::CTRL_H, key::CTRL_F, 's', 's', Key::ENTER]);

    $result = text(label: 'What is your name?');

    expect($result)->toBe('Jess');
});

test('move to the beginning and end of line', function () {
    Prompt::fake(['A', 'r', Key::HOME[0], 's', KEY::END[0], 'c', Key::HOME[1], 's', Key::END[1], 'h', Key::HOME[2], 'e', Key::END[2], 'e', Key::HOME[3], 'J', Key::END[3], 'r', Key::ENTER]);

    $result = text(label: 'What is your name?');

    expect($result)->toBe('JessArcher');
});

it('returns an empty string when non-interactive', function () {
    Prompt::interactive(false);

    $result = text('What is your name?');

    expect($result)->toBe('');
});

it('returns the default value when non-interactive', function () {
    Prompt::interactive(false);

    $result = text('What is your name?', default: 'Taylor');

    expect($result)->toBe('Taylor');
});

it('validates the default value when non-interactive', function () {
    Prompt::interactive(false);

    text('What is your name?', required: true);
})->throws(NonInteractiveValidationException::class, 'Required.');
