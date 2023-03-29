<?php

use Laravel\Prompts\Key;
use Laravel\Prompts\Prompt;
use function Laravel\Prompts\confirm;

it('confirms', function () {
    Prompt::fake([Key::ENTER]);

    $result = confirm(message: 'Are you sure?');

    expect($result)->toBeTrue();
});

test('arrow keys change the value', function () {
    Prompt::fake([Key::DOWN, Key::ENTER]);

    $result = confirm(message: 'Are you sure?');

    expect($result)->toBeFalse();
});

test('the y selects yes', function () {
    Prompt::fake(['y', Key::ENTER]);

    $result = confirm(message: 'Are you sure?');

    expect($result)->toBeTrue();
});

test('the n selects no', function () {
    Prompt::fake(['n', Key::ENTER]);

    $result = confirm(message: 'Are you sure?');

    expect($result)->toBeFalse();
});

it('accepts a default value', function () {
    Prompt::fake([Key::ENTER]);

    $result = confirm(
        message: 'Are you sure?',
        default: false
    );

    expect($result)->toBeFalse();
});
