<?php

use function Laravel\Prompts\spin;
use Laravel\Prompts\Prompt;

it('renders a spinner while executing a callback and then returns the value', function () {
    Prompt::fake([])
        ->expects('write')
        ->with(Mockery::on(fn ($text) => str_contains($text, 'Running...')));

    $result = spin(function () {
        usleep(1000);

        return 'done';
    }, 'Running...');

    expect($result)->toBe('done');
});
