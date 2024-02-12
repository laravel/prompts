<?php

use Laravel\Prompts\Key;
use Laravel\Prompts\Prompt;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\select;
use function Laravel\Prompts\steps;
use function Laravel\Prompts\text;

it('allows stepping through multiple prompts', function () {
    Prompt::fake(['y', 'e', 's', Key::ENTER, Key::DOWN, Key::ENTER, Key::ENTER]);

    $results = steps()
        ->add(fn() => text(label: 'Are you sure?'))
        ->add(fn($label) => select('Items', ['one', 'two', 'three']))
        ->add(fn($number) => confirm('Are you sure you\'re sure?'))
        ->display();

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

    $results = steps()
        ->add(fn() => text(label: 'Are you sure?'))
        ->add(fn($number) => confirm('Are you sure you\'re sure?'))
        ->display();

    expect($results)->toBe(['no', true]);
});

it('displays step numbers', function () {
    Prompt::fake([Key::ENTER, Key::ENTER, Key::ENTER]);

    steps('Custom Title')
        ->add(fn() => confirm('Are you sure?'))
        ->add(fn($number) => confirm('Are you sure you\'re sure?'))
        ->add(fn($number) => confirm('Are you sure you\'re sure you\'re sure?'))
        ->display();

    Prompt::assertStrippedOutputContains('Custom Title 1/3');
    Prompt::assertStrippedOutputContains('Custom Title 2/3');
    Prompt::assertStrippedOutputContains('Custom Title 3/3');
});

it('allows defining a closure to run when going back', function () {
    Prompt::fake([Key::CTRL_U]);

    steps()->add(
        fn() => confirm('Are you sure?'),
        fn() => throw new Exception('Please throw me!')
    )->display();
})->throws('Please throw me!');

it('does not break when reverting the first step', function () {
    Prompt::fake([Key::CTRL_U, Key::CTRL_U, Key::RIGHT, Key::ENTER]);

    $response = steps()->add(fn() => confirm('Are you sure?'))->display();

    expect($response)->toBe([false]);
});

it('allows preventing a step from being reverted', function () {
    Prompt::fake([
        'L', 'u', 'k', 'e',
        Key::ENTER,
        Key::CTRL_U,
        Key::ENTER,
    ]);

    steps('Custom Title')
        ->add(fn() => text('What is your name?'), revert: false)
        ->add(fn($number) => confirm('Are you sure you\'re sure?'))
        ->display();

    Prompt::assertOutputContains('Step 1 cannot be reverted.');
});
