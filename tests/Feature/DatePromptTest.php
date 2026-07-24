<?php

use Laravel\Prompts\DatePrompt;
use Laravel\Prompts\Exceptions\NonInteractiveValidationException;
use Laravel\Prompts\Key;
use Laravel\Prompts\Prompt;

use function Laravel\Prompts\date;

it('returns the default date as a DateTimeImmutable at midnight', function () {
    Prompt::fake([Key::ENTER]);

    $result = date(
        label: 'When should the deploy run?',
        default: '2026-07-24',
    );

    expect($result)->toBeInstanceOf(DateTimeImmutable::class)
        ->and($result->format('Y-m-d H:i:s'))->toBe('2026-07-24 00:00:00');
});

it('accepts a DateTimeInterface default', function () {
    Prompt::fake([Key::ENTER]);

    $result = date(
        label: 'When should the deploy run?',
        default: new DateTime('2026-07-24 15:30:00'),
    );

    expect($result->format('Y-m-d H:i:s'))->toBe('2026-07-24 00:00:00');
});

it('defaults to today', function () {
    Prompt::fake([Key::ENTER]);

    $result = date(label: 'When should the deploy run?');

    expect($result->format('Y-m-d H:i:s'))
        ->toBe((new DateTimeImmutable('today'))->format('Y-m-d H:i:s'));
});

it('navigates days with the left and right arrow keys', function () {
    Prompt::fake([Key::RIGHT, Key::RIGHT, Key::LEFT, Key::ENTER]);

    $result = date(
        label: 'When should the deploy run?',
        default: '2026-07-24',
    );

    expect($result->format('Y-m-d'))->toBe('2026-07-25');
});

it('navigates weeks with the up and down arrow keys across month boundaries', function () {
    Prompt::fake([Key::UP, Key::DOWN, Key::DOWN, Key::ENTER]);

    $result = date(
        label: 'When should the deploy run?',
        default: '2026-07-01',
    );

    expect($result->format('Y-m-d'))->toBe('2026-07-08');
});

it('navigates months with page up and page down, clamping the day', function () {
    Prompt::fake([Key::PAGE_DOWN, Key::ENTER]);

    $result = date(
        label: 'When should the deploy run?',
        default: '2026-01-31',
    );

    expect($result->format('Y-m-d'))->toBe('2026-02-28');
});

it('navigates back a month with page up', function () {
    Prompt::fake([Key::PAGE_DOWN, Key::PAGE_UP, Key::ENTER]);

    $result = date(
        label: 'When should the deploy run?',
        default: '2026-01-31',
    );

    expect($result->format('Y-m-d'))->toBe('2026-01-28');
});

it('navigates years with shift up and shift down, clamping leap days', function () {
    Prompt::fake([Key::SHIFT_DOWN, Key::ENTER]);

    $result = date(
        label: 'When should the deploy run?',
        default: '2024-02-29',
    );

    expect($result->format('Y-m-d'))->toBe('2025-02-28');
});

it('jumps to the first and last day of the month with home and end', function () {
    Prompt::fake([Key::END[0], Key::ENTER]);

    $result = date(
        label: 'When should the deploy run?',
        default: '2026-07-24',
    );

    expect($result->format('Y-m-d'))->toBe('2026-07-31');

    Prompt::fake([Key::HOME[0], Key::ENTER]);

    $result = date(
        label: 'When should the deploy run?',
        default: '2026-07-24',
    );

    expect($result->format('Y-m-d'))->toBe('2026-07-01');
});

it('transforms values', function () {
    Prompt::fake([Key::ENTER]);

    $result = date(
        label: 'When should the deploy run?',
        default: '2026-07-24',
        transform: fn (DateTimeImmutable $date) => $date->format('Y-m-d'),
    );

    expect($result)->toBe('2026-07-24');
});

it('validates', function () {
    Prompt::fake([Key::ENTER, Key::LEFT, Key::ENTER]);

    $result = date(
        label: 'When should the deploy run?',
        default: '2026-07-25',
        validate: fn (DateTimeImmutable $date) => $date->format('N') >= 6
            ? 'The deploy cannot run on a weekend.'
            : null,
    );

    expect($result->format('Y-m-d'))->toBe('2026-07-24');

    Prompt::assertOutputContains('The deploy cannot run on a weekend.');
});

it('rejects an invalid default string', function () {
    date(label: 'When should the deploy run?', default: 'not-a-date');
})->throws(InvalidArgumentException::class, 'not-a-date');

it('rejects a week start outside of Sunday through Saturday', function () {
    date(label: 'When should the deploy run?', weekStartsOn: 7);
})->throws(InvalidArgumentException::class, 'weekStartsOn');

it('can be cancelled', function () {
    Prompt::fake([Key::CTRL_C]);

    date(label: 'When should the deploy run?', default: '2026-07-24');

    Prompt::assertOutputContains('Cancelled.');
});

it('returns the default when non-interactive', function () {
    Prompt::interactive(false);

    $result = date(label: 'When should the deploy run?', default: '2026-07-24');

    expect($result->format('Y-m-d'))->toBe('2026-07-24');
});

it('returns null when non-interactive without a default', function () {
    Prompt::interactive(false);

    expect(date(label: 'When should the deploy run?'))->toBeNull();
});

it('fails when non-interactive and required without a default', function () {
    Prompt::interactive(false);

    date(label: 'When should the deploy run?', required: true);
})->throws(NonInteractiveValidationException::class, 'Required.');

it('fails when non-interactive with a default outside of the range', function () {
    Prompt::interactive(false);

    date(label: 'When should the deploy run?', default: '2026-07-24', min: '2026-08-01');
})->throws(NonInteractiveValidationException::class, 'Must be on or after 2026-08-01.');

it('renders the calendar grid for the highlighted month', function () {
    Prompt::fake([Key::ENTER]);

    date(label: 'When should the deploy run?', default: '2026-07-24');

    Prompt::assertStrippedOutputContains('July 2026');
    Prompt::assertStrippedOutputContains('Mon Tue Wed Thu Fri Sat Sun');
    Prompt::assertStrippedOutputContains('1   2   3   4   5');
    Prompt::assertStrippedOutputContains('6   7   8   9  10  11  12');
    Prompt::assertStrippedOutputContains('13  14  15  16  17  18  19');
    Prompt::assertStrippedOutputContains('27  28  29  30  31');
});

it('starts the week on Sunday when requested', function () {
    Prompt::fake([Key::ENTER]);

    date(label: 'When should the deploy run?', default: '2026-07-24', weekStartsOn: 0);

    Prompt::assertStrippedOutputContains('Sun Mon Tue Wed Thu Fri Sat');
    Prompt::assertStrippedOutputContains('5   6   7   8   9  10  11');
});

it('highlights the selected day', function () {
    Prompt::fake([Key::ENTER]);

    date(label: 'When should the deploy run?', default: '2026-07-24');

    Prompt::assertOutputContains("\e[7m 24\e[27m");
});

it('renders the submitted date', function () {
    Prompt::fake([Key::ENTER]);

    date(label: 'When should the deploy run?', default: '2026-07-24');

    Prompt::assertStrippedOutputContains('2026-07-24');
});

it('jumps to a typed date', function () {
    Prompt::fake(['2', '0', '2', '6', '1', '2', '2', '5', Key::ENTER]);

    $result = date(label: 'When should the deploy run?', default: '2026-07-24');

    expect($result->format('Y-m-d'))->toBe('2026-12-25');
});

it('renders the typed digits over the mask', function () {
    Prompt::fake(['2', '0', '2', '6', '1', '2', '2', '5', Key::ENTER]);

    date(label: 'When should the deploy run?', default: '2026-07-24');

    Prompt::assertStrippedOutputContains('2026-1_-__');
});

it('removes typed digits with backspace', function () {
    Prompt::fake(['2', '0', '2', '6', '1', '3', Key::BACKSPACE, '2', '2', '5', Key::ENTER]);

    $result = date(label: 'When should the deploy run?', default: '2026-07-24');

    expect($result->format('Y-m-d'))->toBe('2026-12-25');
});

it('discards the typed buffer when navigating', function () {
    Prompt::fake(['2', '0', '2', '7', Key::RIGHT, Key::ENTER]);

    $result = date(label: 'When should the deploy run?', default: '2026-07-24');

    expect($result->format('Y-m-d'))->toBe('2026-07-25');
});

it('requires a complete typed date', function () {
    Prompt::fake(['2', '0', '2', '6', Key::ENTER, '1', '2', '2', '5', Key::ENTER]);

    $result = date(label: 'When should the deploy run?', default: '2026-07-24');

    expect($result->format('Y-m-d'))->toBe('2026-12-25');

    Prompt::assertOutputContains('Incomplete date.');
});

it('rejects an impossible typed date', function () {
    Prompt::fake([
        '2', '0', '2', '6', '0', '2', '3', '0', Key::ENTER,
        Key::BACKSPACE, Key::BACKSPACE, '2', '8', Key::ENTER,
    ]);

    $result = date(label: 'When should the deploy run?', default: '2026-07-24');

    expect($result->format('Y-m-d'))->toBe('2026-02-28');

    Prompt::assertOutputContains('Invalid date.');
});

it('ignores non-digit input', function () {
    Prompt::fake(['a', '!', Key::ENTER]);

    $result = date(label: 'When should the deploy run?', default: '2026-07-24');

    expect($result->format('Y-m-d'))->toBe('2026-07-24');
});

it('ignores escape sequences while typing', function () {
    Prompt::fake([
        '2', '0', Key::DELETE, "\e[1;5C", '2', '6', '1', '2', '2', '5',
        Key::BACKSPACE, Key::BACKSPACE, Key::BACKSPACE, Key::BACKSPACE,
        Key::BACKSPACE, Key::BACKSPACE, Key::BACKSPACE, Key::BACKSPACE,
        Key::ENTER,
    ]);

    $result = date(label: 'When should the deploy run?', default: '2026-07-24');

    expect($result->format('Y-m-d'))->toBe('2026-12-25');
});

it('clamps navigation to the min date', function () {
    Prompt::fake([Key::LEFT, Key::LEFT, Key::ENTER]);

    $result = date(
        label: 'When should the deploy run?',
        default: '2026-07-02',
        min: '2026-07-01',
    );

    expect($result->format('Y-m-d'))->toBe('2026-07-01');
});

it('clamps month navigation to the max date', function () {
    Prompt::fake([Key::PAGE_DOWN, Key::ENTER]);

    $result = date(
        label: 'When should the deploy run?',
        default: '2026-07-24',
        max: '2026-08-05',
    );

    expect($result->format('Y-m-d'))->toBe('2026-08-05');
});

it('clamps the default into the range', function () {
    Prompt::fake([Key::ENTER]);

    $result = date(
        label: 'When should the deploy run?',
        default: '2026-07-24',
        min: '2026-08-01',
    );

    expect($result->format('Y-m-d'))->toBe('2026-08-01');
});

it('dims days outside of the range', function () {
    Prompt::fake([Key::ENTER]);

    date(
        label: 'When should the deploy run?',
        default: '2026-07-24',
        min: '2026-07-20',
        max: '2026-07-28',
    );

    Prompt::assertOutputContains("\e[2m 19\e[22m");
    Prompt::assertOutputContains("\e[2m 29\e[22m");
});

it('rejects typed dates before the min date', function () {
    Prompt::fake([
        '2', '0', '2', '6', '0', '1', '0', '1', Key::ENTER,
        Key::BACKSPACE, Key::BACKSPACE, Key::BACKSPACE, Key::BACKSPACE, '0', '7', '0', '5', Key::ENTER,
    ]);

    $result = date(
        label: 'When should the deploy run?',
        default: '2026-07-24',
        min: '2026-07-01',
        max: '2026-07-31',
    );

    expect($result->format('Y-m-d'))->toBe('2026-07-05');

    Prompt::assertOutputContains('Must be on or after 2026-07-01.');
});

it('rejects typed dates after the max date', function () {
    Prompt::fake(['2', '0', '2', '7', '0', '1', '0', '1', Key::ENTER, Key::LEFT, Key::ENTER]);

    $result = date(
        label: 'When should the deploy run?',
        default: '2026-07-24',
        max: '2026-12-31',
    );

    expect($result->format('Y-m-d'))->toBe('2026-07-23');

    Prompt::assertOutputContains('Must be on or before 2026-12-31.');
});

it('rejects a min date after the max date', function () {
    date(label: 'When should the deploy run?', min: '2026-07-31', max: '2026-07-01');
})->throws(InvalidArgumentException::class, 'min');

it('supports custom validation', function () {
    Prompt::validateUsing(function (Prompt $prompt) {
        expect($prompt)
            ->label->toBe('When should the deploy run?')
            ->validate->toBe('weekday');

        return $prompt->validate === 'weekday' && $prompt->value()->format('N') >= 6
            ? 'The deploy cannot run on a weekend.'
            : null;
    });

    Prompt::fake([Key::ENTER, Key::LEFT, Key::ENTER]);

    $result = date(
        label: 'When should the deploy run?',
        default: '2026-07-25',
        validate: 'weekday',
    );

    expect($result->format('Y-m-d'))->toBe('2026-07-24');

    Prompt::assertOutputContains('The deploy cannot run on a weekend.');

    Prompt::validateUsing(fn () => null);
});

it('can fall back', function () {
    Prompt::fallbackWhen(true);

    DatePrompt::fallbackUsing(function (DatePrompt $prompt) {
        expect($prompt->label)->toBe('When should the deploy run?');

        return new DateTimeImmutable('2026-01-01');
    });

    $result = date(label: 'When should the deploy run?');

    expect($result->format('Y-m-d'))->toBe('2026-01-01');
});
