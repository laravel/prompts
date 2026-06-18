<?php

use Laravel\Prompts\Exceptions\NonInteractiveValidationException;
use Laravel\Prompts\Exceptions\SkippedValueValidationException;
use Laravel\Prompts\Key;
use Laravel\Prompts\Prompt;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

it('short-circuits the prompt when skipWhen is provided', function () {
    Prompt::fake([]);

    $result = text(label: 'What is your name?', skipWhen: 'Taylor');

    expect($result)->toBe('Taylor');
});

it('runs transform on the skipped value', function () {
    Prompt::fake([]);

    $result = text(
        label: 'What is your name?',
        skipWhen: ' Taylor ',
        transform: fn ($value) => trim($value),
    );

    expect($result)->toBe('Taylor');
});

it('runs validate on the skipped value', function () {
    Prompt::fake([]);

    $result = text(
        label: 'What is your name?',
        skipWhen: 'ok',
        validate: fn ($value) => null,
    );

    expect($result)->toBe('ok');
});

it('gives skipWhen precedence over default', function () {
    Prompt::fake([]);

    $result = text(
        label: 'What is your name?',
        default: 'Jess',
        skipWhen: 'Taylor',
    );

    expect($result)->toBe('Taylor');
});

it('treats skipWhen null as not provided', function () {
    Prompt::fake(['J', 'e', 's', 's', Key::ENTER]);

    $result = text(label: 'What is your name?', skipWhen: null);

    expect($result)->toBe('Jess');
});

it('throws when a skipped value fails the required check', function () {
    Prompt::fake([]);

    text(label: 'What is your name?', required: true, skipWhen: '');
})->throws(SkippedValueValidationException::class, 'Required.');

it('throws when a skipped value fails the validator', function () {
    Prompt::fake([]);

    text(
        label: 'What is your name?',
        validate: fn ($value) => $value !== 'Jess' ? 'Invalid name.' : null,
        skipWhen: 'Taylor',
    );
})->throws(SkippedValueValidationException::class, 'Invalid name.');

it('keeps skipWhen backwards-compatible for catches targeting NonInteractiveValidationException', function () {
    Prompt::fake([]);

    text(label: 'What is your name?', required: true, skipWhen: '');
})->throws(NonInteractiveValidationException::class);

it('lets skipWhen win over required when the value is present', function () {
    Prompt::fake([]);

    $result = text(label: 'What is your name?', required: true, skipWhen: 'Taylor');

    expect($result)->toBe('Taylor');
});

it('honors skipWhen in non-interactive mode', function () {
    Prompt::interactive(false);

    $result = text(label: 'What is your name?', skipWhen: 'Taylor');

    expect($result)->toBe('Taylor');

    Prompt::interactive(true);
});

it('throws with SkippedValueValidationException in non-interactive mode when invalid', function () {
    Prompt::interactive(false);

    try {
        text(label: 'What is your name?', required: true, skipWhen: '');
    } finally {
        Prompt::interactive(true);
    }
})->throws(SkippedValueValidationException::class, 'Required.');

it('short-circuits confirm when skipWhen is provided', function () {
    Prompt::fake([]);

    $result = confirm(label: 'Continue?', skipWhen: true);

    expect($result)->toBeTrue();
});

it('short-circuits select when skipWhen is provided', function () {
    Prompt::fake([]);

    $result = select(label: 'Pick one', options: ['red', 'blue'], skipWhen: 'blue');

    expect($result)->toBe('blue');
});
