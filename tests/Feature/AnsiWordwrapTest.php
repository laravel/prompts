<?php

use Laravel\Prompts\Themes\Default\Concerns\InteractsWithStrings;

$instance = new class
{
    use InteractsWithStrings;

    protected int $minWidth = 0;

    public function wrap(string $text, int $width): array
    {
        return $this->ansiWordwrap($text, $width);
    }
};

it('wraps plain text without ANSI codes', function () use ($instance) {
    $result = $instance->wrap('Hello World', 5);

    expect($result)->toBe(['Hello', 'World']);
});

it('returns single line when text fits within width', function () use ($instance) {
    $result = $instance->wrap('Hello', 80);

    expect($result)->toBe(['Hello']);
});

it('preserves ANSI codes across word wrap', function () use ($instance) {
    $result = $instance->wrap("\e[31mHello World\e[0m", 5);

    expect($result)->toHaveCount(2);
    // First line should have the red code and close
    expect($result[0])->toContain("\e[31m");
    expect($result[0])->toContain('Hello');
    // Second line should re-apply the red code
    expect($result[1])->toContain("\e[31m");
    expect($result[1])->toContain('World');
});

it('handles text with color change mid-wrap', function () use ($instance) {
    $result = $instance->wrap("\e[31mRed\e[0m \e[32mGreen text here\e[0m", 10);

    expect($result[0])->toContain('Red');
    expect($result[0])->toContain('Green');
    // "text here" should wrap to next line with green
    expect(count($result))->toBeGreaterThanOrEqual(2);
});

it('handles empty string', function () use ($instance) {
    $result = $instance->wrap('', 80);

    expect($result)->toBe(['']);
});

it('closes open ANSI codes at end of wrapped lines', function () use ($instance) {
    $result = $instance->wrap("\e[1mBold text that should wrap around\e[0m", 10);

    // Each line with active codes should end with a reset
    foreach ($result as $line) {
        if (str_contains($line, "\e[1m")) {
            expect($line)->toEndWith("\e[0m");
        }
    }
});

it('wraps text with multi-byte characters and ANSI codes', function () use ($instance) {
    $result = $instance->wrap("\e[31mHêllo Wörld\e[0m", 6);

    expect($result)->toHaveCount(2);
    expect($result[0])->toContain('Hêllo');
    expect($result[1])->toContain('Wörld');
});

it('handles multiple color segments wrapping across lines', function () use ($instance) {
    $text = "\e[31mRed\e[0m \e[32mGreen\e[0m \e[34mBlue\e[0m";
    $result = $instance->wrap($text, 5);

    // Each color word should be on its own line
    expect($result)->toHaveCount(3);
    expect($result[0])->toContain('Red');
    expect($result[1])->toContain('Green');
    expect($result[2])->toContain('Blue');
});

it('preserves unstyled text that does not need wrapping', function () use ($instance) {
    $result = $instance->wrap('Short', 80);

    expect($result)->toBe(['Short']);
});
