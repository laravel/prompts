<?php

use Laravel\Prompts\Prompt;
use Laravel\Prompts\Stream;

use function Laravel\Prompts\stream;

it('renders appended text', function () {
    Prompt::fake();

    $stream = stream();
    $stream->append('Hello, ');
    $stream->append('World!');
    $stream->close();

    Prompt::assertOutputContains('Hello, ');
    Prompt::assertOutputContains('World!');
});

it('returns the full message as the value', function () {
    Prompt::fake();

    $stream = stream();
    $stream->append('Hello, ');
    $stream->append('World!');
    $stream->close();

    expect($stream->value())->toBe('Hello, World!');
});

it('accumulates the message property', function () {
    Prompt::fake();

    $stream = stream();
    $stream->append('foo');
    $stream->append('bar');
    $stream->append('baz');

    // After enough appends exceed fading colors count, earlier messages move to $message
    $stream->close();

    expect($stream->value())->toBe('foobarbaz');
});

it('throws when prompt is called', function () {
    Prompt::fake();

    $stream = new Stream;
    $stream->prompt();
})->throws(RuntimeException::class, 'Stream cannot be prompted');

it('returns lines from the stream', function () {
    Prompt::fake();

    $stream = stream();
    $stream->append('Hello');

    $lines = $stream->lines();

    expect($lines)->toBeArray();
    expect(count($lines))->toBeGreaterThanOrEqual(1);
});

it('wraps long lines', function () {
    Prompt::fake();

    $stream = stream();

    // Append a very long string that should wrap
    $longText = str_repeat('word ', 100);
    $stream->append($longText);
    $stream->close();

    $lines = $stream->lines();

    expect(count($lines))->toBeGreaterThan(1);
});

it('handles newlines in appended text', function () {
    Prompt::fake();

    $stream = stream();
    $stream->append("Line 1\nLine 2\nLine 3");
    $stream->close();

    expect($stream->value())->toBe("Line 1\nLine 2\nLine 3");

    $lines = $stream->lines();

    expect(count($lines))->toBeGreaterThanOrEqual(3);
});

it('handles empty appends', function () {
    Prompt::fake();

    $stream = stream();
    $stream->append('');
    $stream->append('Hello');
    $stream->append('');
    $stream->close();

    expect($stream->value())->toBe('Hello');
});

it('can be created via helper function', function () {
    Prompt::fake();

    $stream = stream();

    expect($stream)->toBeInstanceOf(Stream::class);
});
