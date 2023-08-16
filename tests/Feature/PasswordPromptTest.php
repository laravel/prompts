<?php

use Laravel\Prompts\Key;
use Laravel\Prompts\PasswordPrompt;
use Laravel\Prompts\Prompt;

use function Laravel\Prompts\password;

it('returns the input', function () {
    Prompt::fake(['p', 'a', 's', 's', Key::ENTER]);

    $result = password(label: 'What is the password?');

    expect($result)->toBe('pass');
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
