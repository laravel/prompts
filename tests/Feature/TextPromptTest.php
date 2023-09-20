<?php

use Laravel\Prompts\Exceptions\NonInteractiveValidationException;
use Laravel\Prompts\Key;
use Laravel\Prompts\Prompt;
use Laravel\Prompts\TextPrompt;

use function Laravel\Prompts\text;

it('renders and returns input', function () {
    Prompt::fake()
        ->hidesCursor()
        ->outputs(<<<'OUTPUT'

         ┌ What is your name? ──────────────────────────────────────────┐
         │                                                              │
         └──────────────────────────────────────────────────────────────┘


        OUTPUT)
        ->receives(['Jess', Key::ENTER])
        ->outputs(<<<'OUTPUT'

         ┌ What is your name? ──────────────────────────────────────────┐
         │ Jess                                                         │
         └──────────────────────────────────────────────────────────────┘


        OUTPUT)
        ->showsCursor();

    $result = text(label: 'What is your name?');

    expect($result)->toBe('Jess');
});

it('accepts a default value', function () {
    Prompt::fake()
        ->hidesCursor()
        ->outputs(<<<'OUTPUT'

         ┌ What is your name? ──────────────────────────────────────────┐
         │ Jess                                                         │
         └──────────────────────────────────────────────────────────────┘


        OUTPUT)
        ->receives(Key::ENTER);

    $result = text(
        label: 'What is your name?',
        default: 'Jess'
    );

    expect($result)->toBe('Jess');
});

it('validates', function () {
    Prompt::fake()
        ->receives(['J', 'e', 's'])
        ->outputs(<<<'OUTPUT'

         ┌ What is your name? ──────────────────────────────────────────┐
         │ Jes                                                          │
         └──────────────────────────────────────────────────────────────┘


        OUTPUT)
        ->receives(Key::ENTER)
        ->outputs(<<<'OUTPUT'

         ┌ What is your name? ──────────────────────────────────────────┐
         │ Jes                                                          │
         └──────────────────────────────────────────────────────────────┘
          ⚠ Invalid name.

        OUTPUT)
        ->receives('s')
        ->outputs(<<<'OUTPUT'

         ┌ What is your name? ──────────────────────────────────────────┐
         │ Jess                                                         │
         └──────────────────────────────────────────────────────────────┘


        OUTPUT)
        ->receives(Key::ENTER);

    $result = text(
        label: 'What is your name?',
        validate: fn ($value) => $value !== 'Jess' ? 'Invalid name.' : '',
    );

    expect($result)->toBe('Jess');
});

it('cancels', function () {
    Prompt::fake()
        ->receives(Key::CTRL_C)
        ->outputs(<<<'OUTPUT'

         ┌ What is your name? ──────────────────────────────────────────┐
         │                                                              │
         └──────────────────────────────────────────────────────────────┘
          ⚠ Cancelled.


        OUTPUT);

    text(label: 'What is your name?');
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
    Prompt::fake(['e', 's', Key::HOME, 'J', KEY::END, 's', Key::ENTER]);

    $result = text(label: 'What is your name?');

    expect($result)->toBe('Jess');
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
