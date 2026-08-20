<?php

use Laravel\Prompts\Themes\Default\Concerns\InteractsWithStrings;

$instance = new class
{
    use InteractsWithStrings;

    protected int $minWidth = 0;

    public function strip(string $text): string
    {
        return $this->stripEscapeSequences($text);
    }
};

it('strips a single Symfony inline style tag', function () use ($instance) {
    $result = $instance->strip('<fg=green>Your action?</>');

    expect($result)->toBe('Your action?');
});

it('strips nested Symfony inline style tags', function () use ($instance) {
    $result = $instance->strip('<fg=green>Your action, <fg=yellow>UserName</>?</>');

    expect($result)->toBe('Your action, UserName?');
});

it('strips deeply nested Symfony inline style tags', function () use ($instance) {
    $result = $instance->strip('<fg=green>A<fg=yellow>B<fg=red>C</>D</>E</>');

    expect($result)->toBe('ABCDE');
});

it('strips multiple sibling nested tags', function () use ($instance) {
    $result = $instance->strip('<fg=green>Hello <fg=yellow>World</></> and <fg=red>Foo <fg=blue>Bar</></>');

    expect($result)->toBe('Hello World and Foo Bar');
});
