<?php

use Laravel\Prompts\Exceptions\NonInteractiveValidationException;
use Laravel\Prompts\Key;
use Laravel\Prompts\Prompt;
use Laravel\Prompts\TextPrompt;

use function Laravel\Prompts\text;

afterEach(function () {
    Prompt::cancelUsing(null);
});

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

it('transforms values', function () {
    Prompt::fake([Key::SPACE, 'J', 'e', 's', 's', Key::TAB, Key::ENTER]);

    $result = text(
        label: 'What is your name?',
        transform: fn ($value) => trim($value),
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

it('supports custom validation', function () {
    Prompt::validateUsing(function (Prompt $prompt) {
        expect($prompt)
            ->label->toBe('What is your name?')
            ->validate->toBe('min:2');

        return $prompt->validate === 'min:2' && strlen($prompt->value()) < 2 ? 'Minimum 2 chars!' : null;
    });

    Prompt::fake(['J', Key::ENTER, 'e', 's', 's', Key::ENTER]);

    $result = text(
        label: 'What is your name?',
        validate: 'min:2',
    );

    expect($result)->toBe('Jess');

    Prompt::assertOutputContains('Minimum 2 chars!');

    Prompt::validateUsing(fn () => null);
});

it('allows customizing the cancellation', function () {
    Prompt::cancelUsing(fn () => throw new Exception('Cancelled.'));
    Prompt::fake([Key::CTRL_C]);

    text('What is your name?');
})->throws(Exception::class, 'Cancelled.');

it('handles a failed terminal read gracefully', function () {
    Prompt::fake(['', Key::ENTER]);

    $result = text('What is your name?');

    expect($result)->toBe('');
});
