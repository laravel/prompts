<?php

use Laravel\Prompts\Key;
use Laravel\Prompts\Prompt;
use Laravel\Prompts\TextPrompt;

use function Laravel\Prompts\text;

it('wraps each redraw in a synchronized update', function () {
    Prompt::fake(['J', 'e', Key::ENTER]);

    text(label: 'What is your name?');

    $content = Prompt::content();
    $redraws = substr_count($content, "\e[J");

    expect($redraws)->toBeGreaterThan(0)
        ->and(substr_count($content, "\e[?2026h"))->toBe($redraws)
        ->and(substr_count($content, "\e[?2026l"))->toBe($redraws)
        ->and(preg_match_all('/\e\[\?2026h(?:(?!\e\[\?2026[hl]).)*\e\[J(?:(?!\e\[\?2026[hl]).)*\e\[\?2026l/s', $content))->toBe($redraws);
});

it('does not wrap the first render', function () {
    Prompt::fake([Key::ENTER]);

    text(label: 'What is your name?', default: 'Jess');

    expect(Prompt::content())->not->toStartWith("\e[?2026h");
});

it('keeps the trailing new lines of the redrawn frame', function () {
    Prompt::fake(['J', Key::ENTER, Key::ENTER]);

    text(label: 'What is your name?');

    $next = new TextPrompt(label: 'And your city?', default: 'Bern');
    $next->prompt();

    expect($next->newLinesWritten())->toBe(1);
});
