<?php

use Laravel\Prompts\Output\BufferedConsoleOutput;
use Laravel\Prompts\Prompt;
use Laravel\Prompts\Spinner;

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

it('renders a spinner statically when output is not a terminal even if it is decorated', function () {
    Prompt::fake();
    Prompt::terminal()->shouldReceive('isOutputTty')->andReturn(false);

    $output = new BufferedConsoleOutput;
    $output->setDecorated(true);
    Prompt::setOutput($output);

    $spinner = new Spinner('Running...');

    $result = $spinner->spin(fn () => 'done');

    expect($result)->toBe('done');
    expect($spinner->static)->toBeTrue();
    expect($output->content())->toContain('Running...');
});

it('animates the spinner when output is a decorated terminal', function () {
    Prompt::fake();

    $output = new BufferedConsoleOutput;
    $output->setDecorated(true);
    Prompt::setOutput($output);

    $spinner = new Spinner('Running...');

    $spinner->spin(fn () => 'done');

    expect($spinner->static)->toBeFalse();
});

it('restores the previous signal handler', function () {
    Prompt::fake();

    $originalSignalHandler = pcntl_signal_get_handler(SIGINT);
    $signalHandler = fn () => null;
    pcntl_signal(SIGINT, $signalHandler);

    try {
        spin(fn () => 'done', 'Running...');

        expect(pcntl_signal_get_handler(SIGINT))->toBe($signalHandler);
    } finally {
        pcntl_signal(SIGINT, $originalSignalHandler);
    }
});
