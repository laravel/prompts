<?php

use function Laravel\Prompts\confirm;
use Laravel\Prompts\Key;
use Laravel\Prompts\Prompt;

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
    Prompt::fake([Key::ENTER])
        ->expects('write')
        ->with(Mockery::on(fn ($output) => str_contains($output, 'Sí, por favor') && str_contains($output, 'No, gracias')));

    $result = confirm(
        label: '¿Estás seguro?',
        yes: 'Sí, por favor',
        no: 'No, gracias'
    );

    expect($result)->toBeTrue();
});
