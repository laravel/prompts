<?php

use Laravel\Prompts\Exceptions\NonInteractiveValidationException;
use Laravel\Prompts\Key;
use Laravel\Prompts\PasswordPrompt;
use Laravel\Prompts\Prompt;

use function Laravel\Prompts\password;

it('returns the input', function () {
    Prompt::fake(['p', 'a', 's', 's', Key::ENTER]);

    $result = password(label: 'What is the password?');

    expect($result)->toBe('pass');
});

it('transforms values', function () {
    Prompt::fake(['p', 'a', 's', 's', 'w', 'o', 'r', 'd', Key::ENTER]);

    $dontUseInProduction = md5('password');

    $result = password(
        label: 'What is the password?',
        transform: fn ($value) => md5($value)
    );

    expect($result)->toBe($dontUseInProduction);
});

it('validates', function () {
    Prompt::fake(['p', 'a', 's', Key::ENTER, 's', Key::ENTER]);

    $result = password(
        label: 'What is the password',
        validate: fn ($value) => strlen($value) < 4 ? 'Password must be at least 4 characters.' : '',
    );

    expect($result)->toBe('pass');

    Prompt::assertOutputContains('Password must be at least 4 characters.');
});

it('cancels', function () {
    Prompt::fake([Key::CTRL_C]);

    password(label: 'What is the password');

    Prompt::assertOutputContains('Cancelled.');
});

test('the backspace key removes a character', function () {
    Prompt::fake(['p', 'a', 'z', Key::BACKSPACE, 's', 's', Key::ENTER]);

    $result = password(label: 'What is the password?');

    expect($result)->toBe('pass');
});

test('the delete key removes a character', function () {
    Prompt::fake(['p', 'a', 'z', Key::LEFT, Key::DELETE, 's', 's', Key::ENTER]);

    $result = password(label: 'What is the password?');

    expect($result)->toBe('pass');
});

it('can fall back', function () {
    Prompt::fallbackWhen(true);

    PasswordPrompt::fallbackUsing(function (PasswordPrompt $prompt) {
        expect($prompt->label)->toBe('What is the password?');

        return 'result';
    });

    $result = password('What is the password?');

    expect($result)->toBe('result');
});

it('returns an empty string when non-interactive', function () {
    Prompt::interactive(false);

    $result = password('What is the password?');

    expect($result)->toBe('');
});

it('fails validation when non-interactive', function () {
    Prompt::interactive(false);

    password('What is the password?', required: true);
})->throws(NonInteractiveValidationException::class, 'Required.');

it('supports custom validation', function () {
    Prompt::validateUsing(function (Prompt $prompt) {
        expect($prompt)
            ->label->toBe('What is the password?')
            ->validate->toBe('min:8');

        return $prompt->validate === 'min:8' && strlen($prompt->value()) < 8 ? 'Minimum 8 chars!' : null;
    });

    Prompt::fake(['p', Key::ENTER, 'a', 's', 's', 'w', 'o', 'r', 'd', Key::ENTER]);

    $result = password(
        label: 'What is the password?',
        validate: 'min:8',
    );

    expect($result)->toBe('password');

    Prompt::assertOutputContains('Minimum 8 chars!');

    Prompt::validateUsing(fn () => null);
});

it('accepts a single line description', function () {
    Prompt::fake(['s', 'e', 'c', 'r', 'e', 't', Key::ENTER]);

    $result = password(
        label: 'What is your password?',
        description: 'Please enter a secure password for your account.'
    );

    expect($result)->toBe('secret');
    Prompt::assertOutputContains('Please enter a secure password for your account.');
});

it('accepts a multi-line description', function () {
    Prompt::fake(['m', 'y', 'p', 'a', 's', 's', 'w', 'o', 'r', 'd', '1', '2', '3', Key::ENTER]);

    $result = password(
        label: 'Create password',
        description: 'Please create a strong password for your account. Your password should be at least 8 characters long and include a mix of letters and numbers.

For security reasons, your password will be hidden as you type. Make sure to remember it as it cannot be recovered.'
    );

    expect($result)->toBe('mypassword123');

    Prompt::assertOutputContains('Please create a strong password for your account. Your');
    Prompt::assertOutputContains('password should be at least 8 characters long and include a');
    Prompt::assertOutputContains('mix of letters and numbers.');
    Prompt::assertOutputContains('For security reasons, your password will be hidden as you');
    Prompt::assertOutputContains('type. Make sure to remember it as it cannot be recovered.');
});
