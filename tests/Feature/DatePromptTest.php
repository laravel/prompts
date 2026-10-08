<?php

use Laravel\Prompts\DatePrompt;
use Laravel\Prompts\Exceptions\NonInteractiveValidationException;
use Laravel\Prompts\Key;
use Laravel\Prompts\Prompt;

use function Laravel\Prompts\date;

it('renders an inline date input by default', function () {
    Prompt::fake([Key::ENTER]);

    date(label: 'Release date', default: '2026-07-24');

    Prompt::assertStrippedOutputContains('2026-07-24');
    Prompt::assertStrippedOutputDoesntContain('July 2026');
    Prompt::assertOutputContains("\e[7m2026\e[27m");
});

it('can opt into the calendar', function () {
    Prompt::fake([Key::ENTER]);

    date(label: 'Release date', default: '2026-07-24', calendar: true);

    Prompt::assertStrippedOutputContains('July 2026');
});

it('moves inline focus without changing the date', function () {
    Prompt::fake([Key::LEFT, Key::RIGHT, Key::CTRL_F, Key::RIGHT, Key::ENTER]);

    $result = date(label: 'Release date', default: '2026-07-24');

    expect($result->format('Y-m-d'))->toBe('2026-07-24');
    Prompt::assertOutputContains("\e[7m07\e[27m");
    Prompt::assertOutputContains("\e[7m24\e[27m");
});

it('steps inline date segments safely', function ($default, $keys, $expected) {
    Prompt::fake([...$keys, Key::ENTER]);

    $result = date(label: 'Release date', default: $default);

    expect($result->format('Y-m-d'))->toBe($expected);
})->with([
    'leap year' => ['2024-02-29', [Key::UP], '2025-02-28'],
    'short month' => ['2026-01-31', [Key::RIGHT, Key::UP], '2026-02-28'],
    'previous month' => ['2026-03-31', [Key::TAB, Key::DOWN], '2026-02-28'],
    'year boundary' => ['2026-12-31', [Key::SHIFT_TAB, Key::UP], '2027-01-01'],
    'tab wrap' => ['2026-07-24', [Key::TAB, Key::TAB, Key::TAB, Key::DOWN], '2025-07-24'],
]);

it('types an inline date and corrects a segment with backspace', function () {
    Prompt::fake(['2027-13', Key::BACKSPACE, '2-25', Key::ENTER]);

    $result = date(label: 'Release date', default: '2026-07-24');

    expect($result->format('Y-m-d'))->toBe('2027-12-25');
});

it('rejects invalid inline edits before moving focus or submitting', function ($keys, $correction, $error, $expected) {
    Prompt::fake([...$keys, Key::TAB, Key::ENTER, ...$correction, Key::ENTER]);

    $result = date(label: 'Release date', default: '2026-02-24');

    expect($result->format('Y-m-d'))->toBe($expected);
    Prompt::assertOutputContains($error);
})->with([
    'incomplete year' => [['20'], ['26'], 'Incomplete year.', '2026-02-24'],
    'zero month' => [[Key::TAB, '00'], [Key::BACKSPACE, '2'], 'Invalid date.', '2026-02-24'],
    'impossible day' => [[Key::TAB, Key::TAB, '30'], [Key::BACKSPACE, Key::BACKSPACE, '01'], 'Invalid date.', '2026-02-01'],
]);

it('clamps inline stepping but rejects typed dates outside the range', function () {
    Prompt::fake([Key::SHIFT_TAB, Key::UP, '30', Key::ENTER, Key::BACKSPACE, Key::BACKSPACE, '05', Key::ENTER]);

    $result = date(label: 'Release date', default: '2026-07-24', max: '2026-07-25');

    expect($result->format('Y-m-d'))->toBe('2026-07-05');
    Prompt::assertOutputContains('Must be on or before 2026-07-25.');
});

it('preserves the timezone when typing inline segments', function () {
    Prompt::fake(['2027-12-25', Key::ENTER]);

    $result = date(label: 'Release date', default: new DateTimeImmutable('2026-07-24', new DateTimeZone('America/New_York')));

    expect($result->format('Y-m-d e'))->toBe('2027-12-25 America/New_York');
});

it('preserves custom and empty hints in both date views', function ($calendar) {
    Prompt::fake([Key::ENTER]);
    date(label: 'Release date', default: '2026-07-24', hint: 'A custom hint.', calendar: $calendar);
    Prompt::assertStrippedOutputContains('A custom hint.');

    Prompt::fake([Key::ENTER]);
    date(label: 'Release date', default: '2026-07-24', hint: '', calendar: $calendar);
    Prompt::assertStrippedOutputDoesntContain('Use the');
    Prompt::assertStrippedOutputDoesntContain('Left/Right or Tab');
})->with([false, true]);

it('ignores unrelated characters and escape sequences in inline segments', function () {
    Prompt::fake(['a!', Key::DELETE, "\e[1;5C", '2027', Key::ENTER]);

    $result = date(label: 'Release date', default: '2026-07-24');

    expect($result->format('Y-m-d'))->toBe('2027-07-24');
});

it('validates and transforms the committed inline date', function () {
    Prompt::fake(['2027-12-25', Key::ENTER]);

    $validated = null;
    $result = date(
        label: 'Release date',
        default: '2026-07-24',
        transform: fn (DateTimeImmutable $value) => $value->format('Y-m-d'),
        validate: function ($value) use (&$validated) {
            $validated = $value;
        },
    );

    expect($result)->toBe('2027-12-25')->and($validated)->toBe($result);
});

it('does not submit an emptied inline segment', function () {
    Prompt::fake([Key::SHIFT_TAB, Key::BACKSPACE, Key::BACKSPACE, Key::ENTER, '25', Key::ENTER]);

    $result = date(label: 'Release date', default: '2026-07-24');

    expect($result->format('Y-m-d'))->toBe('2026-07-25');
    Prompt::assertOutputContains('Incomplete day.');
});

it('allows valid bounded dates to be entered across multiple segments', function ($keys) {
    Prompt::fake([...$keys, Key::ENTER]);

    $prompt = new DatePrompt('Release date', default: '2026-12-31', min: '2026-12-01', max: '2027-01-31');
    $result = $prompt->prompt();

    expect($prompt->state)->toBe('submit')
        ->and($result->format('Y-m-d'))->toBe('2027-01-15');
})->with([
    'tab' => [['2027', Key::TAB, '01', Key::TAB, '15']],
    'arrows' => [['2027', Key::RIGHT, '01', Key::RIGHT, '15']],
    'paste' => [['2027-01-15']],
]);

it('preserves the first invalid segment in a pasted date', function ($input, $error, $buffer, $display) {
    Prompt::fake([$input, Key::ENTER, Key::CTRL_C]);

    $prompt = new DatePrompt('Release date', default: '2026-07-24');
    $prompt->prompt();

    expect($prompt->state)->toBe('cancel')
        ->and($prompt->segmentBuffer)->toBe($buffer)
        ->and($prompt->formattedValue())->toBe($display);
    Prompt::assertOutputContains($error);
})->with([
    'invalid month' => ['2027-13-01', 'Invalid date.', '13', '2027-13-24'],
    'incomplete year' => ['20-12-25', 'Incomplete year.', '20', '20__-07-24'],
]);

it('stops pasting at the final date segment', function () {
    Prompt::fake(['2027-12-25-01', Key::ENTER]);

    $result = date(label: 'Release date', default: '2026-07-24');

    expect($result->format('Y-m-d'))->toBe('2027-12-25');
});

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
        calendar: true,
    );

    expect($result->format('Y-m-d'))->toBe('2026-07-25');
});

it('navigates weeks with the up and down arrow keys across month boundaries', function () {
    Prompt::fake([Key::UP, Key::DOWN, Key::DOWN, Key::ENTER]);

    $result = date(
        label: 'When should the deploy run?',
        default: '2026-07-01',
        calendar: true,
    );

    expect($result->format('Y-m-d'))->toBe('2026-07-08');
});

it('navigates months with page up and page down, clamping the day', function () {
    Prompt::fake([Key::PAGE_DOWN, Key::ENTER]);

    $result = date(
        label: 'When should the deploy run?',
        default: '2026-01-31',
        calendar: true,
    );

    expect($result->format('Y-m-d'))->toBe('2026-02-28');
});

it('navigates back a month with page up', function () {
    Prompt::fake([Key::PAGE_DOWN, Key::PAGE_UP, Key::ENTER]);

    $result = date(
        label: 'When should the deploy run?',
        default: '2026-01-31',
        calendar: true,
    );

    expect($result->format('Y-m-d'))->toBe('2026-01-28');
});

it('navigates years with shift up and shift down, clamping leap days', function () {
    Prompt::fake([Key::SHIFT_DOWN, Key::SHIFT_DOWN, Key::SHIFT_UP, Key::ENTER]);

    $result = date(
        label: 'When should the deploy run?',
        default: '2024-02-29',
        calendar: true,
    );

    expect($result->format('Y-m-d'))->toBe('2025-02-28');
});

it('jumps to the first and last day of the month with home and end', function () {
    Prompt::fake([Key::END[0], Key::ENTER]);

    $result = date(
        label: 'When should the deploy run?',
        default: '2026-07-24',
        calendar: true,
    );

    expect($result->format('Y-m-d'))->toBe('2026-07-31');

    Prompt::fake([Key::HOME[0], Key::ENTER]);

    $result = date(
        label: 'When should the deploy run?',
        default: '2026-07-24',
        calendar: true,
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
        calendar: true,
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

    date(label: 'When should the deploy run?', default: '2026-07-24', calendar: true);

    Prompt::assertStrippedOutputContains('July 2026');
    Prompt::assertStrippedOutputContains('Mon Tue Wed Thu Fri Sat Sun');
    Prompt::assertStrippedOutputContains('1   2   3   4   5');
    Prompt::assertStrippedOutputContains('6   7   8   9  10  11  12');
    Prompt::assertStrippedOutputContains('13  14  15  16  17  18  19');
    Prompt::assertStrippedOutputContains('27  28  29  30  31');
});

it('starts the week on Sunday when requested', function () {
    Prompt::fake([Key::ENTER]);

    date(label: 'When should the deploy run?', default: '2026-07-24', weekStartsOn: 0, calendar: true);

    Prompt::assertStrippedOutputContains('Sun Mon Tue Wed Thu Fri Sat');
    Prompt::assertStrippedOutputContains('5   6   7   8   9  10  11');
});

it('highlights the selected day', function () {
    Prompt::fake([Key::ENTER]);

    date(label: 'When should the deploy run?', default: '2026-07-24', calendar: true);

    Prompt::assertOutputContains("\e[7m 24\e[27m");
});

it('renders the submitted date', function () {
    Prompt::fake([Key::ENTER]);

    date(label: 'When should the deploy run?', default: '2026-07-24');

    Prompt::assertStrippedOutputContains('2026-07-24');
});

it('jumps to a typed date', function () {
    Prompt::fake(['2', '0', '2', '6', '1', '2', '2', '5', Key::ENTER]);

    $result = date(label: 'When should the deploy run?', default: '2026-07-24', calendar: true);

    expect($result->format('Y-m-d'))->toBe('2026-12-25');
});

it('renders the typed digits over the mask', function () {
    Prompt::fake(['2', '0', '2', '6', '1', '2', '2', '5', Key::ENTER]);

    date(label: 'When should the deploy run?', default: '2026-07-24', calendar: true);

    Prompt::assertStrippedOutputContains('2026-1_-__');
});

it('removes typed digits with backspace', function () {
    Prompt::fake(['2', '0', '2', '6', '1', '3', Key::BACKSPACE, '2', '2', '5', Key::ENTER]);

    $result = date(label: 'When should the deploy run?', default: '2026-07-24', calendar: true);

    expect($result->format('Y-m-d'))->toBe('2026-12-25');
});

it('discards the typed buffer when navigating', function () {
    Prompt::fake(['2', '0', '2', '7', Key::RIGHT, Key::ENTER]);

    $result = date(label: 'When should the deploy run?', default: '2026-07-24', calendar: true);

    expect($result->format('Y-m-d'))->toBe('2026-07-25');
});

it('requires a complete typed date', function () {
    Prompt::fake(['2', '0', '2', '6', Key::ENTER, '1', '2', '2', '5', Key::ENTER]);

    $result = date(label: 'When should the deploy run?', default: '2026-07-24', calendar: true);

    expect($result->format('Y-m-d'))->toBe('2026-12-25');

    Prompt::assertOutputContains('Incomplete date.');
});

it('rejects an impossible typed date', function () {
    Prompt::fake([
        '2', '0', '2', '6', '0', '2', '3', '0', Key::ENTER,
        Key::BACKSPACE, Key::BACKSPACE, '2', '8', Key::ENTER,
    ]);

    $result = date(label: 'When should the deploy run?', default: '2026-07-24', calendar: true);

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

    $result = date(label: 'When should the deploy run?', default: '2026-07-24', calendar: true);

    expect($result->format('Y-m-d'))->toBe('2026-12-25');
});

it('clamps navigation to the min date', function () {
    Prompt::fake([Key::LEFT, Key::LEFT, Key::ENTER]);

    $result = date(
        label: 'When should the deploy run?',
        default: '2026-07-02',
        min: '2026-07-01',
        calendar: true,
    );

    expect($result->format('Y-m-d'))->toBe('2026-07-01');
});

it('clamps month navigation to the max date', function () {
    Prompt::fake([Key::PAGE_DOWN, Key::ENTER]);

    $result = date(
        label: 'When should the deploy run?',
        default: '2026-07-24',
        max: '2026-08-05',
        calendar: true,
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
        calendar: true,
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
        calendar: true,
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
        calendar: true,
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
        calendar: true,
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
