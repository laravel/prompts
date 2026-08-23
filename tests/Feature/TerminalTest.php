<?php

use Laravel\Prompts\Terminal;

it('still detects true color support from COLORTERM on non-Windows platforms', function () {
    $property = new ReflectionProperty(Terminal::class, 'trueColorSupport');
    $property->setAccessible(true);
    $property->setValue(null, null);

    $previous = getenv('COLORTERM');
    putenv('COLORTERM=truecolor');

    try {
        expect((new Terminal)->supportsTrueColor())->toBe(PHP_OS_FAMILY !== 'Windows');
    } finally {
        putenv($previous === false ? 'COLORTERM' : "COLORTERM={$previous}");
        $property->setValue(null, null);
    }
});
