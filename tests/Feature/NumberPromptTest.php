<?php

use Laravel\Prompts\Exceptions\NonInteractiveValidationException;
use Laravel\Prompts\Key;
use Laravel\Prompts\NumberPrompt;
use Laravel\Prompts\Prompt;

use function Laravel\Prompts\number;

afterEach(function () {
    Prompt::cancelUsing(null);
});

it('returns the input', function () {
    Prompt::fake(['1', '0', Key::ENTER]);

    $result = number(label: 'How many items do you want to buy?');

    expect($result)->toBe(10);
});

it('accepts a default value', function () {
    Prompt::fake([Key::ENTER]);

    $result = number(
        label: 'How many items do you want to buy?',
        default: '10'
    );

    expect($result)->toBe(10);
});

it('validates', function () {
    Prompt::fake(['n', 'o', Key::ENTER, Key::BACKSPACE, Key::BACKSPACE, '1', '0', Key::ENTER]);

    $result = number(
        label: 'How many items do you want to buy?',
    );

    expect($result)->toBe(10);

    Prompt::assertOutputContains('Must be a number');
});

it('validates the minimum value', function () {
    Prompt::fake(['0', Key::ENTER, Key::BACKSPACE, '1', Key::ENTER]);

    $result = number(
        label: 'How many items do you want to buy?',
        min: 1,
    );

    expect($result)->toBe(1);

    Prompt::assertOutputContains('Must be at least 1');
});

it('validates the maximum value', function () {
    Prompt::fake(['100', Key::ENTER, Key::BACKSPACE,  Key::BACKSPACE, Key::BACKSPACE, '9', '9', Key::ENTER]);

    $result = number(
        label: 'How many items do you want to buy?',
        max: 99,
    );

    expect($result)->toBe(99);

    Prompt::assertOutputContains('Must be less than 99');
});

it('falls through to the original validation', function () {
    Prompt::fake([
        '100',
        Key::ENTER,
        Key::BACKSPACE,
        Key::BACKSPACE,
        Key::BACKSPACE,
        '9',
        '8',
        Key::ENTER,
        Key::BACKSPACE,
        '9',
        Key::ENTER,
    ]);

    $result = number(
        label: 'How many items do you want to buy?',
        max: 99,
        validate: fn($value) => $value !== 99 ? 'Must be 99' : null,
    );

    expect($result)->toBe(99);

    Prompt::assertOutputContains('Must be less than 99');
    Prompt::assertOutputContains('Must be 99');
});

it('starts with the minimum value when the up arrow is pressed and value is empty', function () {
    Prompt::fake([Key::UP, Key::ENTER]);

    $result = number(
        label: 'How many items do you want to buy?',
        min: 1,
        max: 10,
    );

    expect($result)->toBe(1);
});

it('increases when the up arrow is pressed', function () {
    Prompt::fake(['1', Key::UP, Key::UP, Key::ENTER]);

    $result = number(
        label: 'How many items do you want to buy?',
        min: 1,
        max: 10,
    );

    expect($result)->toBe(3);
});

it('will not increase past the maximum value', function () {
    Prompt::fake(['9', Key::UP, Key::UP, Key::ENTER]);

    $result = number(
        label: 'How many items do you want to buy?',
        min: 1,
        max: 10,
    );

    expect($result)->toBe(10);
});

it('starts with the minimum value when the down arrow is pressed and value is empty', function () {
    Prompt::fake([Key::DOWN, Key::ENTER]);

    $result = number(
        label: 'How many items do you want to buy?',
        min: 1,
        max: 10,
    );

    expect($result)->toBe(10);
});

it('decreases when the down arrow is pressed', function () {
    Prompt::fake(['3', Key::DOWN, Key::DOWN, Key::ENTER]);

    $result = number(
        label: 'How many items do you want to buy?',
        min: 1,
        max: 10,
    );

    expect($result)->toBe(1);
});

it('will not decrease past the minimum value', function () {
    Prompt::fake(['1', Key::DOWN, Key::DOWN, Key::ENTER]);

    $result = number(
        label: 'How many items do you want to buy?',
        min: 1,
        max: 10,
    );

    expect($result)->toBe(1);
});

it('can set the step size', function () {
    Prompt::fake(['1', Key::UP, Key::UP, Key::ENTER]);

    $result = number(
        label: 'How many items do you want to buy?',
        step: 2,
    );

    expect($result)->toBe(5);
});

it('cancels', function () {
    Prompt::fake([Key::CTRL_C]);

    number(label: 'How many items do you want to buy?');

    Prompt::assertOutputContains('Cancelled.');
});

test('the backspace key removes a character', function () {
    Prompt::fake(['1', '0', 's', Key::BACKSPACE, Key::ENTER]);

    $result = number(label: 'How many items do you want to buy?');

    expect($result)->toBe(10);
});

test('the delete key removes a character', function () {
    Prompt::fake(['1', '0', 's', Key::LEFT, Key::DELETE, Key::ENTER]);

    $result = number(label: 'How many items do you want to buy?');

    expect($result)->toBe(10);
});

it('can fall back', function () {
    Prompt::fallbackWhen(true);

    NumberPrompt::fallbackUsing(function (NumberPrompt $prompt) {
        expect($prompt->label)->toBe('How many items do you want to buy?');

        return 'result';
    });

    $result = number(label: 'How many items do you want to buy?');

    expect($result)->toBe('result');
});

test('support emacs style key binding', function () {
    Prompt::fake(['1', 's', '0',  Key::CTRL_B, Key::CTRL_H, key::CTRL_F, Key::ENTER]);

    $result = number(label: 'How many items do you want to buy?');

    expect($result)->toBe(10);
});

it('returns an empty string when non-interactive', function () {
    Prompt::interactive(false);

    $result = number(label: 'How many items do you want to buy?');

    expect($result)->toBe('');
});

it('returns the default value when non-interactive', function () {
    Prompt::interactive(false);

    $result = number(label: 'How many items do you want to buy?', default: '10');

    expect($result)->toBe(10);
});

it('validates the default value when non-interactive', function () {
    Prompt::interactive(false);

    number(label: 'How many items do you want to buy?', required: true);
})->throws(NonInteractiveValidationException::class, 'Required.');

it('allows customizing the cancellation', function () {
    Prompt::cancelUsing(fn() => throw new Exception('Cancelled.'));
    Prompt::fake([Key::CTRL_C]);

    number(label: 'How many items do you want to buy?');
})->throws(Exception::class, 'Cancelled.');
