<?php

use Laravel\Prompts\P;
use Laravel\Prompts\Prompt;

it('renders a spinner while executing a callback and then returns the value', function () {
    Prompt::fake();

    $result = P::spin(function () {
        usleep(1000);

        return 'done';
    }, 'Running...');

    expect($result)->toBe('done');

    Prompt::assertOutputContains('Running...');
});
