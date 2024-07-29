<?php

use Laravel\Prompts\ConfirmPrompt;
use Laravel\Prompts\Exceptions\NonInteractiveValidationException;
use Laravel\Prompts\Key;
use Laravel\Prompts\Prompt;

use function Laravel\Prompts\confirm;

it('confirms', function () {
    Prompt::fake([Key::ENTER]);

    $result = confirm(label: 'Are you sure?');

    expect($result)->toBeTrue();
});

test('arrow keys change the value', function () {
    Prompt::fake([Key::DOWN, Key::ENTER]);

    $result = confirm(label: 'Are you sure?');

    expect($result)->toBeFalse();
});

test('the y selects yes', function () {
    Prompt::fake(['y', Key::ENTER]);

    $result = confirm(label: 'Are you sure?');

    expect($result)->toBeTrue();
});

test('the n selects no', function () {
    Prompt::fake(['n', Key::ENTER]);

    $result = confirm(label: 'Are you sure?');

    expect($result)->toBeFalse();
});

it('accepts a default value', function () {
    Prompt::fake([Key::ENTER]);

    $result = confirm(
        label: 'Are you sure?',
        default: false
    );

    expect($result)->toBeFalse();
});

it('allows the labels to be changed', function () {
    Prompt::fake([Key::ENTER]);

    $result = confirm(
        label: '¿Estás seguro?',
        yes: 'Sí, por favor',
        no: 'No, gracias'
    );

    expect($result)->toBeTrue();

    Prompt::assertOutputContains('Sí, por favor');
    Prompt::assertOutputContains('No, gracias');
});

it('transforms values', function () {
    Prompt::fake([Key::ENTER]);

    $result = confirm(
        label: 'Are you sure?',
        transform: fn ($value) => ! $value,
    );

    expect($result)->toBeFalse();
});

it('validates', function () {
    Prompt::fake([Key::ENTER, 'y', Key::ENTER]);

    $result = confirm(
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

    $result = confirm('Would you like to continue?', false);

    expect($result)->toBeTrue();
});

test('support emacs style key binding', function () {
    Prompt::fake([Key::CTRL_N, Key::ENTER]);

    $result = confirm(label: 'Are you sure?');

    expect($result)->toBeFalse();
});

it('returns the default value when non-interactive', function () {
    Prompt::interactive(false);

    $result = confirm('Would you like to continue?', false);

    expect($result)->toBeFalse();
});

it('validates the default value when non-interactive', function () {
    Prompt::interactive(false);

    confirm(
        'Would you like to continue?',
        default: false,
        required: true,
    );
})->throws(NonInteractiveValidationException::class, 'Required.');

it('supports custom validation', function () {
    Prompt::validateUsing(function (Prompt $prompt) {
        expect($prompt)
            ->label->toBe('Are you sure?')
            ->validate->toBe('confirmed');

        return $prompt->validate === 'confirmed' && ! $prompt->value() ? 'Need to be sure!' : null;
    });

    Prompt::fake([Key::DOWN, Key::ENTER, Key::UP, Key::ENTER]);

    confirm(label: 'Are you sure?', validate: 'confirmed');

    Prompt::assertOutputContains('Need to be sure!');

    Prompt::validateUsing(fn () => null);
});
