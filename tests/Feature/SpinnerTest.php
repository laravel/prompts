<?php

use Laravel\Prompts\Output\BufferedConsoleOutput;
use Laravel\Prompts\Prompt;

use function Laravel\Prompts\spin;

it('renders a spinner while executing a callback and then returns the value', function () {
    Prompt::fake();

    $result = spin(function () {
        usleep(1000);

        return 'done';
    }, 'Running...');

    expect($result)->toBe('done');

    Prompt::assertOutputContains('Running...');
});

it('renders a spinner statically when output is not decorated', function () {
    Prompt::fake();

    $output = new BufferedConsoleOutput;
    $output->setDecorated(false);
    Prompt::setOutput($output);

    $result = spin(fn () => 'done', 'Running...');

    expect($result)->toBe('done');
    expect($output->content())->toContain('Running...');
});
