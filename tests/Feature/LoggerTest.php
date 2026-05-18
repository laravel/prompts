<?php

use Laravel\Prompts\Support\Logger;

it('does not throw when constructed without a socket', function () {
    // Task::renderStatically() constructs the Logger without a socket on
    // platforms where pcntl_fork / posix_kill are unavailable (e.g. Windows).
    // Any logger call from the user-supplied callback must not crash.
    $logger = new Logger('abc123');

    $logger->line('hello');
    $logger->partial('streamed ');
    $logger->commitPartial();
    $logger->success('done');
    $logger->warning('careful');
    $logger->error('broken');
    $logger->label('Updated');
    $logger->subLabel('detail');

    expect(true)->toBeTrue();
});
