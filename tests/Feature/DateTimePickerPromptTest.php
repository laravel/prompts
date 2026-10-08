<?php

use Laravel\Prompts\DateTimePickerPrompt;
use Laravel\Prompts\Key;
use Laravel\Prompts\Prompt;

use function Laravel\Prompts\datetimepicker;

it('edits 12 hour times in both datetime views', function ($calendar, $keys) {
    Prompt::fake([...$keys, Key::ENTER]);

    $result = datetimepicker('Release', default: '2026-07-24 14:30', calendar: $calendar, use12Hours: true);

    expect($result->format('Y-m-d H:i'))->toBe('2026-07-24 00:05');
    Prompt::assertStrippedOutputContains('12:05 AM');
})->with([
    'inline' => [false, [Key::TAB, Key::TAB, Key::TAB, '12:05 AM']],
    'calendar' => [true, [Key::TAB, '12:05 AM']],
]);

it('validates 12 hour datetime edits against full instant bounds', function ($calendar, $keys) {
    Prompt::fake([...$keys, '09:30 AM', Key::ENTER, 'p', Key::ENTER]);

    $result = datetimepicker(
        'Release',
        default: '2026-07-24 14:30',
        min: '2026-07-24 12:00',
        max: '2026-07-24 22:00',
        use12Hours: true,
        calendar: $calendar,
    );

    expect($result->format('Y-m-d H:i'))->toBe('2026-07-24 21:30');
    Prompt::assertOutputContains('Must be on or after 2026-07-24 12:00 PM.');
})->with([
    'inline' => [false, [Key::TAB, Key::TAB, Key::TAB]],
    'calendar' => [true, [Key::TAB]],
]);

it('pastes a full 12 hour datetime including seconds', function () {
    Prompt::fake(['2027-12-25 12:05:45 AM', Key::ENTER]);

    $result = datetimepicker('Release', default: '2026-07-24 14:30:10', withSeconds: true, use12Hours: true);

    expect($result->format('Y-m-d H:i:s'))->toBe('2027-12-25 00:05:45');
    Prompt::assertStrippedOutputContains('2027-12-25 12:05:45 AM');
});

it('remembers period focus when switching between calendar and time', function () {
    Prompt::fake([Key::TAB, Key::RIGHT, Key::RIGHT, Key::TAB, Key::RIGHT, Key::SHIFT_TAB, Key::UP, Key::ENTER]);

    $result = datetimepicker('Release', default: '2026-07-24 14:30', calendar: true, use12Hours: true);

    expect($result->format('Y-m-d H:i'))->toBe('2026-07-25 02:30');
    Prompt::assertOutputContains("\e[7mAM\e[27m");
});

it('renders a single inline datetime input by default', function () {
    Prompt::fake([Key::ENTER]);

    datetimepicker(label: 'Release', default: '2026-07-24 14:30');

    Prompt::assertStrippedOutputContains('2026-07-24 14:30');
    Prompt::assertStrippedOutputDoesntContain('Time  ');
    Prompt::assertStrippedOutputDoesntContain('July 2026');
});

it('edits every inline datetime segment', function () {
    Prompt::fake(['2027-12-25 09:45:12', Key::ENTER]);

    $result = datetimepicker(label: 'Release', default: '2026-07-24 14:30:10', withSeconds: true);

    expect($result->format('Y-m-d H:i:s'))->toBe('2027-12-25 09:45:12');
});

it('steps and wraps inline time segments without changing the day', function () {
    Prompt::fake([Key::TAB, Key::TAB, Key::TAB, Key::UP, Key::RIGHT, Key::DOWN, Key::ENTER]);

    $result = datetimepicker(label: 'Release', default: '2026-07-24 23:00');

    expect($result->format('Y-m-d H:i'))->toBe('2026-07-24 00:59');
    Prompt::assertOutputContains("\e[7m59\e[27m");
});

it('switches directly between calendar and time and remembers the time focus', function () {
    Prompt::fake([Key::TAB, Key::RIGHT, Key::TAB, Key::RIGHT, Key::SHIFT_TAB, Key::UP, Key::ENTER]);

    $result = datetimepicker(label: 'Release', default: '2026-07-24 14:30', calendar: true);

    expect($result->format('Y-m-d H:i'))->toBe('2026-07-25 14:31');
});

it('keeps calendar time arrow focus inside the time row', function () {
    Prompt::fake([Key::TAB, Key::LEFT, Key::UP, Key::RIGHT, Key::RIGHT, Key::DOWN, Key::ENTER]);

    $result = datetimepicker(label: 'Release', default: '2026-07-24 14:30', calendar: true);

    expect($result->format('Y-m-d H:i'))->toBe('2026-07-24 15:29');
});

it('validates inline time edits against the full instant', function () {
    Prompt::fake([Key::SHIFT_TAB, '15', Key::ENTER, Key::BACKSPACE, Key::BACKSPACE, '25', Key::ENTER]);

    $result = datetimepicker(label: 'Release', default: '2026-07-24 09:30', min: '2026-07-24 09:20');

    expect($result->format('Y-m-d H:i'))->toBe('2026-07-24 09:25');
    Prompt::assertOutputContains('Must be on or after 2026-07-24 09:20.');
});

it('rejects invalid time segments without normalizing them', function ($calendar, $withSeconds, $keys, $typed, $correction) {
    Prompt::fake([...$keys, $typed, Key::ENTER, Key::BACKSPACE, Key::BACKSPACE, $correction, Key::ENTER]);

    $result = datetimepicker(label: 'Release', default: '2026-07-24 14:30:10', calendar: $calendar, withSeconds: $withSeconds);

    expect($result->format('Y-m-d'))->toBe('2026-07-24');
    Prompt::assertOutputContains('Invalid time.');
})->with([
    'inline hour' => [false, false, [Key::TAB, Key::TAB, Key::TAB], '24', '23'],
    'calendar minute' => [true, false, [Key::TAB, Key::RIGHT], '60', '59'],
    'inline second' => [false, true, [Key::SHIFT_TAB], '60', '59'],
]);

it('commits a buffered calendar date once its time is corrected', function () {
    Prompt::fake(['20260724', Key::ENTER, Key::TAB, '10', Key::ENTER]);

    $validated = null;
    $result = datetimepicker(
        label: 'Release',
        default: '2026-07-25 09:30',
        min: '2026-07-24 10:00',
        calendar: true,
        validate: function ($value) use (&$validated) {
            $validated = $value;
        },
    );

    expect($result->format('Y-m-d H:i'))->toBe('2026-07-24 10:30')
        ->and($validated)->toEqual($result);
    Prompt::assertOutputContains('Must be on or after 2026-07-24 10:00.');
});

it('clamps datetime date navigation to the full instant boundaries', function ($calendar, $keys) {
    Prompt::fake([...$keys, Key::ENTER]);

    $result = datetimepicker(label: 'Release', default: '2026-07-25 09:30', min: '2026-07-24 10:00', calendar: $calendar);

    expect($result->format('Y-m-d H:i'))->toBe('2026-07-24 10:00');
})->with([
    'inline' => [false, [Key::TAB, Key::TAB, Key::DOWN]],
    'calendar' => [true, [Key::LEFT]],
]);

it('wraps inline seconds and tabs back to the year', function () {
    Prompt::fake([Key::SHIFT_TAB, Key::DOWN, Key::TAB, Key::UP, Key::ENTER]);

    $result = datetimepicker(label: 'Release', default: '2026-07-24 14:30:00', withSeconds: true);

    expect($result->format('Y-m-d H:i:s'))->toBe('2027-07-24 14:30:59');
});

it('preserves datetime hints and renders mode specific defaults', function ($calendar) {
    Prompt::fake([Key::ENTER]);
    datetimepicker(label: 'Release', default: '2026-07-24 14:30', calendar: $calendar);
    Prompt::assertStrippedOutputContains($calendar ? 'Tab: calendar/time.' : 'Left/Right or Tab: move.');

    Prompt::fake([Key::ENTER]);
    datetimepicker(label: 'Release', default: '2026-07-24 14:30', hint: 'Custom hint.', calendar: $calendar);
    Prompt::assertStrippedOutputContains('Custom hint.');

    Prompt::fake([Key::ENTER]);
    datetimepicker(label: 'Release', default: '2026-07-24 14:30', hint: '', calendar: $calendar);
    Prompt::assertStrippedOutputDoesntContain('Tab: calendar/time');
    Prompt::assertStrippedOutputDoesntContain('Left/Right or Tab');
})->with([false, true]);

it('allows valid bounded times to be entered across multiple segments', function ($calendar, $keys) {
    Prompt::fake([...$keys, Key::ENTER]);

    $prompt = new DateTimePickerPrompt(
        'Release',
        default: '2026-07-24 09:30',
        min: '2026-07-24 09:30',
        max: '2026-07-24 10:15',
        calendar: $calendar,
    );
    $result = $prompt->prompt();

    expect($prompt->state)->toBe('submit')
        ->and($result->format('Y-m-d H:i'))->toBe('2026-07-24 10:00');
})->with([
    'inline' => [false, [Key::TAB, Key::TAB, Key::TAB, '10', Key::RIGHT, '00']],
    'calendar' => [true, [Key::TAB, '10', Key::RIGHT, '00']],
]);

it('preserves the invalid time segment in a pasted datetime', function () {
    Prompt::fake(['2027-12-25 24:01', Key::ENTER, Key::CTRL_C]);

    $prompt = new DateTimePickerPrompt('Release', default: '2026-07-24 14:30');
    $prompt->prompt();

    expect($prompt->state)->toBe('cancel')
        ->and($prompt->focused)->toBe('hour')
        ->and($prompt->segmentBuffer)->toBe('24')
        ->and($prompt->formattedValue())->toBe('2027-12-25 24:30');
    Prompt::assertOutputContains('Invalid time.');
});

it('stops pasting at the final time segment', function ($withSeconds, $input, $expected) {
    Prompt::fake([$input, Key::ENTER]);

    $result = datetimepicker(label: 'Release', default: '2026-07-24 14:30:10', withSeconds: $withSeconds);

    expect($result->format('Y-m-d H:i:s'))->toBe($expected);
})->with([
    'minutes' => [false, '2027-12-25 09:45:12', '2027-12-25 09:45:00'],
    'seconds' => [true, '2027-12-25 09:45:12:59', '2027-12-25 09:45:12'],
]);

it('commits the pending calendar date with its time before validation and transformation', function () {
    Prompt::fake(['20260724', Key::ENTER, Key::TAB, '10', Key::ENTER]);

    $validated = null;
    $prompt = new DateTimePickerPrompt(
        'Release',
        default: '2026-07-25 09:30',
        min: '2026-07-24 10:00',
        max: '2026-07-25 09:45',
        calendar: true,
        transform: fn (DateTimeImmutable $value) => $value->format('Y-m-d H:i'),
        validate: function ($value) use (&$validated) {
            $validated = $value;
        },
    );
    $result = $prompt->prompt();

    expect($prompt->state)->toBe('submit')
        ->and($result)->toBe('2026-07-24 10:30')
        ->and($validated)->toBe($result)
        ->and($prompt->buffer)->toBe('');
});

it('preserves incomplete and invalid calendar masks while editing time', function ($input, $error) {
    Prompt::fake([$input, Key::TAB, '10', Key::ENTER, Key::CTRL_C]);

    $prompt = new DateTimePickerPrompt('Release', default: '2026-07-25 09:30', calendar: true);
    $prompt->prompt();

    expect($prompt->state)->toBe('cancel')
        ->and($prompt->buffer)->toBe($input);
    Prompt::assertOutputContains($error);
})->with([
    'incomplete' => ['202607', 'Incomplete date.'],
    'invalid' => ['20260732', 'Invalid date.'],
]);

it('returns the default datetime with the seconds zeroed', function () {
    Prompt::fake([Key::ENTER]);

    $result = datetimepicker(
        label: 'When should the deploy run?',
        default: '2026-07-24 14:30:45',
    );

    expect($result)->toBeInstanceOf(DateTimeImmutable::class)
        ->and($result->format('Y-m-d H:i:s'))->toBe('2026-07-24 14:30:00');
});

it('edits the hour with the arrow keys after tabbing', function () {
    Prompt::fake([Key::TAB, Key::UP, Key::ENTER]);

    $result = datetimepicker(
        label: 'When should the deploy run?',
        default: '2026-07-24 14:30',
        calendar: true,
    );

    expect($result->format('Y-m-d H:i'))->toBe('2026-07-24 15:30');
});

it('wraps the hour around midnight', function () {
    Prompt::fake([Key::TAB, Key::UP, Key::ENTER]);

    $result = datetimepicker(
        label: 'When should the deploy run?',
        default: '2026-07-24 23:30',
        calendar: true,
    );

    expect($result->format('H:i'))->toBe('00:30');

    Prompt::fake([Key::TAB, Key::DOWN, Key::ENTER]);

    $result = datetimepicker(
        label: 'When should the deploy run?',
        default: '2026-07-24 00:15',
        calendar: true,
    );

    expect($result->format('H:i'))->toBe('23:15');
});

it('types into the focused time segment', function () {
    Prompt::fake([Key::TAB, Key::RIGHT, '4', '5', Key::ENTER]);

    $result = datetimepicker(
        label: 'When should the deploy run?',
        default: '2026-07-24 14:30',
        calendar: true,
    );

    expect($result->format('H:i'))->toBe('14:45');
});

it('replaces the selected time segment with typed digits', function () {
    Prompt::fake([Key::TAB, '0', '9', Key::ENTER]);

    $result = datetimepicker(
        label: 'When should the deploy run?',
        default: '2026-07-24 14:30',
        calendar: true,
    );

    expect($result->format('H:i'))->toBe('09:30');

    Prompt::fake([Key::TAB, Key::RIGHT, '3', '7', Key::ENTER]);

    $result = datetimepicker(
        label: 'When should the deploy run?',
        default: '2026-07-24 14:59',
        calendar: true,
    );

    expect($result->format('H:i'))->toBe('14:37');
});

it('ignores escape sequences while a time segment is focused', function () {
    Prompt::fake([Key::TAB, Key::PAGE_UP, Key::SHIFT_UP, Key::ENTER]);

    $result = datetimepicker(
        label: 'When should the deploy run?',
        default: '2026-07-24 14:30',
        calendar: true,
    );

    expect($result->format('Y-m-d H:i'))->toBe('2026-07-24 14:30');
});

it('moves between time segments with the arrow keys', function () {
    Prompt::fake([Key::TAB, Key::RIGHT, '4', '5', Key::ENTER]);

    $result = datetimepicker(
        label: 'When should the deploy run?',
        default: '2026-07-24 14:30',
        calendar: true,
    );

    expect($result->format('H:i'))->toBe('14:45');
});

it('cycles the focus back to the calendar with tab', function () {
    Prompt::fake([Key::TAB, Key::TAB, Key::RIGHT, Key::ENTER]);

    $result = datetimepicker(
        label: 'When should the deploy run?',
        default: '2026-07-24 14:30',
        calendar: true,
    );

    expect($result->format('Y-m-d H:i'))->toBe('2026-07-25 14:30');
});

it('switches to time editing with shift tab', function () {
    Prompt::fake([Key::SHIFT_TAB, Key::RIGHT, '4', '5', Key::ENTER]);

    $result = datetimepicker(
        label: 'When should the deploy run?',
        default: '2026-07-24 14:30',
        calendar: true,
    );

    expect($result->format('H:i'))->toBe('14:45');
});

it('returns to the calendar with shift tab from the hour', function () {
    Prompt::fake([Key::TAB, Key::SHIFT_TAB, Key::DOWN, Key::ENTER]);

    $result = datetimepicker(
        label: 'When should the deploy run?',
        default: '2026-07-24 14:30',
        calendar: true,
    );

    expect($result->format('Y-m-d H:i'))->toBe('2026-07-31 14:30');
});

it('supports a seconds segment', function () {
    Prompt::fake([Key::TAB, Key::RIGHT, Key::RIGHT, Key::UP, Key::ENTER]);

    $result = datetimepicker(
        label: 'When should the deploy run?',
        default: '2026-07-24 14:30:10',
        withSeconds: true,
        calendar: true,
    );

    expect($result->format('Y-m-d H:i:s'))->toBe('2026-07-24 14:30:11');
});

it('still accepts typed dates while the calendar is focused', function () {
    Prompt::fake(['2', '0', '2', '6', '1', '2', '2', '5', Key::ENTER]);

    $result = datetimepicker(
        label: 'When should the deploy run?',
        default: '2026-07-24 14:30',
        calendar: true,
    );

    expect($result->format('Y-m-d H:i'))->toBe('2026-12-25 14:30');
});

it('renders the time row and highlights the focused segment', function () {
    Prompt::fake([Key::TAB, Key::ENTER]);

    datetimepicker(label: 'When should the deploy run?', default: '2026-07-24 14:30', calendar: true);

    Prompt::assertStrippedOutputContains('Time  14:30');
    Prompt::assertStrippedOutputContains('2026-07-24 14:30');
    Prompt::assertOutputContains("\e[7m14\e[27m");
});

it('rejects times outside of the range', function () {
    Prompt::fake([Key::TAB, Key::DOWN, Key::ENTER, Key::UP, Key::ENTER]);

    $result = datetimepicker(
        label: 'When should the deploy run?',
        default: '2026-07-24 09:30',
        min: '2026-07-24 09:00',
        calendar: true,
    );

    expect($result->format('Y-m-d H:i'))->toBe('2026-07-24 09:30');

    Prompt::assertOutputContains('Must be on or after 2026-07-24 09:00.');
});

it('ignores microseconds in the range boundaries', function () {
    Prompt::interactive(false);

    $result = datetimepicker(
        label: 'When should the maintenance window start?',
        default: '2026-07-24 09:00:00.900000',
        min: '2026-07-24 09:00:00.500000',
        withSeconds: true,
    );

    expect($result->format('Y-m-d H:i:s.u'))->toBe('2026-07-24 09:00:00.000000');
});

it('returns the default when non-interactive', function () {
    Prompt::interactive(false);

    $result = datetimepicker(label: 'When should the deploy run?', default: '2026-07-24 14:30');

    expect($result->format('Y-m-d H:i'))->toBe('2026-07-24 14:30');
});

it('returns null when non-interactive without a default', function () {
    Prompt::interactive(false);

    expect(datetimepicker(label: 'When should the deploy run?'))->toBeNull();
});
