<?php

use Laravel\Prompts\Terminal;
use Symfony\Component\Console\Output\StreamOutput;

it('detects that a stream output that is not a terminal is not a tty', function () {
    $output = new StreamOutput(fopen('php://memory', 'w'));

    expect((new Terminal)->isOutputTty($output))->toBeFalse();
});
