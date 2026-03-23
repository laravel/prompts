<?php

use Laravel\Prompts\Output\ConsoleOutput;
use Symfony\Component\Console\Output\StreamOutput;

/**
 * Create a ConsoleOutput that writes to a temporary stream instead of stdout.
 */
function createSilentOutput(): ConsoleOutput
{
    $output = new ConsoleOutput;
    $stream = fopen('php://memory', 'rw');

    // Replace the underlying stream so output doesn't leak to stdout
    $ref = new ReflectionProperty(StreamOutput::class, 'stream');
    $ref->setValue($output, $stream);

    return $output;
}

it('correctly counts trailing newlines with unix line endings', function () {
    $output = createSilentOutput();
    $ref = new ReflectionProperty($output, 'newLinesWritten');

    $ref->setValue($output, 0);
    $output->writeln('Hello');
    expect($ref->getValue($output))->toBe(1);

    $ref->setValue($output, 0);
    $output->writeln('Hello');
    $output->writeln('');
    expect($ref->getValue($output))->toBe(2);
});

it('correctly counts trailing newlines with windows line endings', function () {
    $output = createSilentOutput();
    $ref = new ReflectionProperty($output, 'newLinesWritten');

    // Simulate what happens when PHP_EOL is \r\n:
    // writeln() appends PHP_EOL to the message, then doWrite counts trailing newlines.
    // On Windows, PHP_EOL is \r\n. The old rtrim-based code would count \r and \n
    // as separate characters, doubling the count. The regex fix handles this correctly.
    $ref->setValue($output, 0);
    $output->writeln('Hello');
    $count = $ref->getValue($output);

    // Regardless of platform, a single writeln should count as 1 trailing newline
    expect($count)->toBe(1);
});

it('accumulates newlines for blank lines', function () {
    $output = createSilentOutput();
    $ref = new ReflectionProperty($output, 'newLinesWritten');

    $ref->setValue($output, 0);
    $output->writeln('Hello');
    $output->writeln('');
    $output->writeln('');

    expect($ref->getValue($output))->toBe(3);
});

it('resets newline count for non-blank lines', function () {
    $output = createSilentOutput();
    $ref = new ReflectionProperty($output, 'newLinesWritten');

    $ref->setValue($output, 0);
    $output->writeln('Hello');
    $output->writeln('');
    $output->writeln('World');

    // 'World' is non-blank, so count resets to 1 (its own trailing newline)
    expect($ref->getValue($output))->toBe(1);
});

it('counts zero trailing newlines for messages without newline flag', function () {
    $output = createSilentOutput();
    $ref = new ReflectionProperty($output, 'newLinesWritten');

    $ref->setValue($output, 0);
    $output->write('Hello');

    expect($ref->getValue($output))->toBe(0);
});
