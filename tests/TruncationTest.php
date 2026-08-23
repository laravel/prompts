<?php

declare(strict_types=1);

use Laravel\Prompts\Concerns\Truncation;

$truncate = new class
{
    use Truncation;

    public function t(string $string, int $width): string
    {
        return $this->truncate($string, $width);
    }
};

it('removes an escape sequence that was cut in the middle by truncation', function () use ($truncate): void {
    $result = $truncate->t("\e[38;2;255;0;0m".str_repeat('a', 40), 12);

    expect($result)->not->toContain("\e");
});

it('keeps complete escape sequences when the cut lands between them', function () use ($truncate): void {
    $result = $truncate->t("\e[31mred\e[0m ".str_repeat('a', 30), 8);

    expect($result)->toBe("\e[31mre…");
});

it('does not modify strings without escape sequences', function () use ($truncate): void {
    expect($truncate->t(str_repeat('a', 30), 8))->toBe('aaaaaaa…');
});
