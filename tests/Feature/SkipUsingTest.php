<?php

use Laravel\Prompts\Exceptions\NonInteractiveValidationException;
use Laravel\Prompts\Exceptions\SkippedValueValidationException;
use Laravel\Prompts\Key;
use Laravel\Prompts\Prompt;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

it('short-circuits the prompt when skipUsing is provided', function () {
    Prompt::fake([]);

    $result = text(label: 'What is your name?', skipUsing: 'Taylor');

    expect($result)->toBe('Taylor');
});

it('runs transform on the skipped value', function () {
    Prompt::fake([]);

    $result = text(
        label: 'What is your name?',
        skipUsing: ' Taylor ',
        transform: fn ($value) => trim($value),
    );

    expect($result)->toBe('Taylor');
});

it('runs validate on the skipped value', function () {
    Prompt::fake([]);

    $result = text(
        label: 'What is your name?',
        skipUsing: 'ok',
        validate: fn ($value) => null,
    );

    expect($result)->toBe('ok');
});

it('gives skipUsing precedence over default', function () {
    Prompt::fake([]);

    $result = text(
        label: 'What is your name?',
        default: 'Jess',
        skipUsing: 'Taylor',
    );

    expect($result)->toBe('Taylor');
});

it('treats skipUsing null as not provided', function () {
    Prompt::fake(['J', 'e', 's', 's', Key::ENTER]);

    $result = text(label: 'What is your name?', skipUsing: null);

    expect($result)->toBe('Jess');
});

it('resolves a skipUsing closure lazily', function () {
    Prompt::fake([]);

    $result = text(label: 'What is your name?', skipUsing: fn () => 'Taylor');

    expect($result)->toBe('Taylor');
});

it('prompts normally when a skipUsing closure returns null', function () {
    Prompt::fake(['J', 'e', 's', 's', Key::ENTER]);

    $result = text(label: 'What is your name?', skipUsing: fn () => null);

    expect($result)->toBe('Jess');
});

it('throws when a skipped value fails the required check', function () {
    Prompt::fake([]);

    text(label: 'What is your name?', required: true, skipUsing: '');
})->throws(SkippedValueValidationException::class, 'Required.');

it('throws when a skipped value fails the validator', function () {
    Prompt::fake([]);

    text(
        label: 'What is your name?',
        validate: fn ($value) => $value !== 'Jess' ? 'Invalid name.' : null,
        skipUsing: 'Taylor',
    );
})->throws(SkippedValueValidationException::class, 'Invalid name.');

it('keeps skipUsing backwards-compatible for catches targeting NonInteractiveValidationException', function () {
    Prompt::fake([]);

    text(label: 'What is your name?', required: true, skipUsing: '');
})->throws(NonInteractiveValidationException::class);

it('lets skipUsing win over required when the value is present', function () {
    Prompt::fake([]);

    $result = text(label: 'What is your name?', required: true, skipUsing: 'Taylor');

    expect($result)->toBe('Taylor');
});

it('honors skipUsing in non-interactive mode', function () {
    Prompt::interactive(false);

    $result = text(label: 'What is your name?', skipUsing: 'Taylor');

    expect($result)->toBe('Taylor');

    Prompt::interactive(true);
});

it('throws with SkippedValueValidationException in non-interactive mode when invalid', function () {
    Prompt::interactive(false);

    try {
        text(label: 'What is your name?', required: true, skipUsing: '');
    } finally {
        Prompt::interactive(true);
    }
})->throws(SkippedValueValidationException::class, 'Required.');

it('short-circuits confirm when skipUsing is provided', function () {
    Prompt::fake([]);

    $result = confirm(label: 'Continue?', skipUsing: true);

    expect($result)->toBeTrue();
});

it('short-circuits select when skipUsing is provided', function () {
    Prompt::fake([]);

    $result = select(label: 'Pick one', options: ['red', 'blue'], skipUsing: 'blue');

    expect($result)->toBe('blue');
});
