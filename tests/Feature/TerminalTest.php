<?php

use Laravel\Prompts\Terminal;
use Symfony\Component\Console\Output\StreamOutput;

it('detects that a stream output that is not a terminal is not a tty', function () {
    $output = new StreamOutput(fopen('php://memory', 'w'));

    expect((new Terminal)->isOutputTty($output))->toBeFalse();
});

it('initializes the terminal dimensions without deprecation notices', function () {
    $deprecations = [];

    set_error_handler(function (int $level, string $message) use (&$deprecations) {
        $deprecations[] = $message;

        return true;
    }, E_DEPRECATED);

    try {
        (new Terminal)->initDimensions();
    } finally {
        restore_error_handler();
    }

    expect($deprecations)->toBeEmpty();
});
