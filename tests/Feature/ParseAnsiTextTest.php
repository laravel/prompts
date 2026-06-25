<?php

use Laravel\Prompts\Themes\Default\Concerns\InteractsWithStrings;

$instance = new class
{
    use InteractsWithStrings;

    protected int $minWidth = 0;

    public function parse(string $text): array
    {
        return $this->parseAnsiText($text);
    }
};

it('parses plain text into a single segment', function () use ($instance) {
    $segments = $instance->parse('Hello, World!');

    expect($segments)->toBe([
        ['text' => 'Hello, World!', 'codes' => '', 'link' => ''],
    ]);
});

it('parses text with a single ANSI code', function () use ($instance) {
    $segments = $instance->parse("\e[31mHello\e[0m");

    expect($segments)->toBe([
        ['text' => 'Hello', 'codes' => "\e[31m", 'link' => ''],
    ]);
});

it('parses text with mixed styled and unstyled segments', function () use ($instance) {
    $segments = $instance->parse("Hello \e[1mBold\e[0m World");

    expect($segments)->toBe([
        ['text' => 'Hello ', 'codes' => '', 'link' => ''],
        ['text' => 'Bold', 'codes' => "\e[1m", 'link' => ''],
        ['text' => ' World', 'codes' => '', 'link' => ''],
    ]);
});

it('parses text with multiple consecutive ANSI codes', function () use ($instance) {
    $segments = $instance->parse("\e[31mRed\e[0m \e[32mGreen\e[0m \e[34mBlue\e[0m");

    expect($segments)->toBe([
        ['text' => 'Red', 'codes' => "\e[31m", 'link' => ''],
        ['text' => ' ', 'codes' => '', 'link' => ''],
        ['text' => 'Green', 'codes' => "\e[32m", 'link' => ''],
        ['text' => ' ', 'codes' => '', 'link' => ''],
        ['text' => 'Blue', 'codes' => "\e[34m", 'link' => ''],
    ]);
});

it('parses empty string', function () use ($instance) {
    $segments = $instance->parse('');

    expect($segments)->toBe([]);
});

it('parses text with 24-bit color codes', function () use ($instance) {
    $segments = $instance->parse("\e[38;2;255;100;50mColored\e[0m");

    expect($segments)->toBe([
        ['text' => 'Colored', 'codes' => "\e[38;2;255;100;50m", 'link' => ''],
    ]);
});
