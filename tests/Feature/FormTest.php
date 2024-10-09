<?php

use Laravel\Prompts\Key;
use Laravel\Prompts\Prompt;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\form;
use function Laravel\Prompts\outro;
use function Laravel\Prompts\text;

it('can run multiple steps', function () {
    Prompt::fake([
        'L', 'u', 'k', 'e', Key::ENTER,
        Key::ENTER,
        Key::ENTER,
    ]);

    $responses = form()
        ->text('What is your name?')
        ->select('What is your language?', ['PHP', 'JS'])
        ->confirm('Are you sure?')
        ->submit();

    expect($responses)->toBe([
        'Luke',
        'PHP',
        true,
    ]);
});

it('can revert steps', function () {
    Prompt::fake([
        'L', 'u', 'k', 'e', Key::ENTER,
        Key::ENTER,
        Key::CTRL_U, Key::CTRL_U,
        ...array_fill(0, 4, Key::BACKSPACE),
        'J', 'e', 's', 's', Key::ENTER,
        Key::DOWN, Key::ENTER,
        Key::ENTER,
    ]);

    $responses = form()
        ->text('What is your name?')
        ->select('What is your language?', ['PHP', 'JS'])
        ->confirm('Are you sure?')
        ->submit();

    expect($responses)->toBe([
        'Jess',
        'JS',
        true,
    ]);
});

it('passes all available responses to each step', function () {
    Prompt::fake([
        'L', 'u', 'k', 'e', Key::ENTER,
        Key::ENTER,
        Key::ENTER,
    ]);

    $responses = form()
        ->text('What is your name?')
        ->select('What is your language?', ['PHP', 'JS'])
        ->add(fn ($responses) => confirm("Are you sure your name is {$responses[0]} and your language is {$responses[1]}?"))
        ->submit();

    Prompt::assertOutputContains('Are you sure your name is Luke and your language is PHP?');
});

it('can key a response by a given string', function () {
    Prompt::fake([
        'L', 'u', 'k', 'e', Key::ENTER,
        Key::ENTER,
        Key::ENTER,
    ]);

    $responses = form()
        ->text('What is your name?', name: 'name')
        ->select('What is your language?', ['PHP', 'JS'], name: 'language')
        ->add(fn ($responses) => confirm("Are you sure your name is {$responses['name']} and your language is {$responses['language']}?"))
        ->submit();

    Prompt::assertOutputContains('Are you sure your name is Luke and your language is PHP?');
});

it('does not allow reverting normal prompts', function () {
    Prompt::fake([
        'L', 'u', 'k', 'e', Key::ENTER,
        Key::ENTER,
        Key::CTRL_U,
        Key::ENTER,
    ]);

    form()
        ->text('What is your name?')
        ->select('What is your language?', ['PHP', 'JS'])
        ->submit();

    $confirm = confirm('Are you sure?');

    Prompt::assertOutputContains('This cannot be reverted.');
    expect($confirm)->toBeTrue();
});

it('does not allow reverting the first step', function () {
    Prompt::fake([Key::CTRL_U, Key::ENTER]);

    $responses = form()->confirm('Are you sure?')->submit();

    expect($responses)->toBe([true]);
});

it('skips steps over steps that have no user input when reverting', function () {
    Prompt::fake([
        '3', Key::ENTER,
        Key::CTRL_U,
        '0', Key::ENTER,
        Key::ENTER,
    ]);

    $responses = form()
        ->text('How old are you?')
        ->info('This should be skipped')
        ->alert('This should be skipped')
        ->confirm('Are you sure?')
        ->submit();

    expect($responses)->toBe(['30', null, null, true]);
});

it('will not skip over the first step when reverting', function () {
    Prompt::fake([
        Key::CTRL_U,
        Key::ENTER,
    ]);

    $responses = form()
        ->info('This should not be skipped')
        ->confirm('Are you sure?')
        ->submit();

    expect($responses)->toBe([null, true]);
});

it('prefills existing responses when reverting', function () {
    Prompt::fake([
        'J', 'e', 's', 's', Key::ENTER,
        Key::CTRL_U,
        Key::ENTER,
        Key::ENTER,
    ]);

    $responses = form()
        ->text('What is your name?')
        ->confirm('Are you sure?')
        ->submit();

    expect($responses[0])->toBe('Jess');
});

it('stops steps at the moment of reverting', function () {
    Prompt::fake([
        '2', '7', Key::ENTER,
        Key::DOWN,
        Key::CTRL_U,
        Key::ENTER,
        Key::ENTER,
    ]);

    form()
        ->text('What is your age?')
        ->add(function () {
            $confirmed = confirm('Are you sure?');

            if (! $confirmed) {
                outro('This should not appear!');
            }
        })->submit();

    Prompt::assertOutputDoesntContain('This should not appear!');
});

it('can revert steps with conditions', function () {
    Prompt::fake([
        'L', 'u', 'k', 'e', Key::ENTER, // name
        Key::DOWN, Key::ENTER, // JS
        Key::CTRL_U, // revert
        Key::UP, Key::ENTER, // PHP
        '8', '.', '3', Key::ENTER, // version
        Key::ENTER,
    ]);

    $responses = form()
        ->text('What is your name?')
        ->select('What is your language?', ['PHP', 'JS'])
        ->addIf(fn ($responses) => $responses[1] === 'PHP', fn ($responses) => text('Which version?'))
        ->confirm('Are you sure?')
        ->submit();

    expect($responses)->toBe([
        'Luke',
        'PHP',
        '8.3',
        true,
    ]);
});

it('leaves skipped conditional field empty', function () {
    Prompt::fake([
        'L', 'u', 'k', 'e', Key::ENTER, // name
        Key::DOWN, Key::ENTER, // JS
        Key::ENTER,
    ]);

    $responses = form()
        ->text('What is your name?')
        ->select('What is your language?', ['PHP', 'JS'])
        ->addIf(fn ($responses) => $responses[1] === 'PHP', fn ($responses) => text('Which version?'))
        ->confirm('Are you sure?')
        ->submit();

    expect($responses)->toBe([
        'Luke',
        'JS',
        null,
        true,
    ]);
});
