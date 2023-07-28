<?php

use Laravel\Prompts\ConfirmPrompt;
use Laravel\Prompts\Key;
use Laravel\Prompts\P;
use Laravel\Prompts\Prompt;

it('confirms', function () {
    Prompt::fake([Key::ENTER]);

    $result = P::confirm(label: 'Are you sure?');

    expect($result)->toBeTrue();
});

test('arrow keys change the value', function () {
    Prompt::fake([Key::DOWN, Key::ENTER]);

    $result = P::confirm(label: 'Are you sure?');

    expect($result)->toBeFalse();
});

test('the y selects yes', function () {
    Prompt::fake(['y', Key::ENTER]);

    $result = P::confirm(label: 'Are you sure?');

    expect($result)->toBeTrue();
});

test('the n selects no', function () {
    Prompt::fake(['n', Key::ENTER]);

    $result = P::confirm(label: 'Are you sure?');

    expect($result)->toBeFalse();
});

it('accepts a default value', function () {
    Prompt::fake([Key::ENTER]);

    $result = P::confirm(
        label: 'Are you sure?',
        default: false
    );

    expect($result)->toBeFalse();
});

it('allows the labels to be changed', function () {
    Prompt::fake([Key::ENTER]);

    $result = P::confirm(
        label: '¿Estás seguro?',
        yes: 'Sí, por favor',
        no: 'No, gracias'
    );

    expect($result)->toBeTrue();

    Prompt::assertOutputContains('Sí, por favor');
    Prompt::assertOutputContains('No, gracias');
});

it('validates', function () {
    Prompt::fake([Key::ENTER, 'y', Key::ENTER]);

    $result = P::confirm(
        label: 'Would you like to continue?',
        default: false,
        validate: fn ($value) => $value === false ? 'You must choose yes.' : null,
    );

    expect($result)->toBeTrue();

    Prompt::assertOutputContains('You must choose yes.');
});

it('can fall back', function () {
    Prompt::fallbackWhen(true);

    ConfirmPrompt::fallbackUsing(function (ConfirmPrompt $prompt) {
        expect($prompt->label)->toBe('Would you like to continue?');

        return true;
    });

    $result = P::confirm('Would you like to continue?', false);

    expect($result)->toBeTrue();
});
