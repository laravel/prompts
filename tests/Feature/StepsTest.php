<?php

use Laravel\Prompts\Key;
use Laravel\Prompts\Prompt;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\info;
use function Laravel\Prompts\select;
use function Laravel\Prompts\steps;
use function Laravel\Prompts\text;

it('allows stepping through multiple prompts', function () {
    Prompt::fake(['y', 'e', 's', Key::ENTER, Key::DOWN, Key::ENTER, Key::ENTER]);

    $results = steps(fn() => text(label: 'Are you sure?'))
        ->then(fn($label) => select('Items', ['one', 'two', 'three']))
        ->then(fn($number) => confirm('Are you sure you\'re sure?'))
        ->run();

    expect($results)->toBe(['yes', 'two', true]);

    Prompt::assertOutputContains('Are you sure?');
    Prompt::assertOutputContains('Items');
    Prompt::assertOutputContains('Are you sure you\'re sure?');
});

it('allows reverting steps', function () {
    Prompt::fake([
        'y', 'e', 's',
        Key::ENTER,
        Key::CTRL_U,
        'n', 'o',
        Key::ENTER,
        Key::ENTER
    ]);

    $results = steps(fn() => text(label: 'Are you sure?'))
        ->then(fn($number) => confirm('Are you sure you\'re sure?'))
        ->run();

    expect($results)->toBe(['no', true]);
});

it('displays step numbers', function () {
    Prompt::fake([Key::ENTER, Key::ENTER, Key::ENTER]);

    steps(fn() => confirm('Are you sure?'))
        ->then(fn($number) => confirm('Are you sure you\'re sure?'))
        ->then(fn($number) => confirm('Are you sure you\'re sure you\'re sure?'))
        ->run();

    Prompt::assertOutputContains('1 of 3');
    Prompt::assertOutputContains('2 of 3');
    Prompt::assertOutputContains('3 of 3');
});

it('allows defining a closure to run when going back', function () {
    Prompt::fake([Key::CTRL_U]);

    steps(
        fn () => confirm('Are you sure?'),
        fn () => throw new Exception('Please throw me!')
    )->run();
})->throws('Please throw me!');

it('does not break when reverting the first step', function () {
    Prompt::fake([Key::CTRL_U, Key::CTRL_U, Key::RIGHT, Key::ENTER]);

    $response = steps(fn() => confirm('Are you sure?'))->run();

    expect($response)->toBe([false]);
});
