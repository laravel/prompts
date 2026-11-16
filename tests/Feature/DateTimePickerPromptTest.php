<?php

use Laravel\Prompts\DateTimePickerPrompt;
use Laravel\Prompts\Key;
use Laravel\Prompts\Prompt;

use function Laravel\Prompts\datetimepicker;

it('edits 12 hour times', function () {
    Prompt::fake([Key::TAB, Key::TAB, Key::TAB, '12:05 AM', Key::ENTER]);

    $result = datetimepicker('Release', default: '2026-07-24 14:30', use12Hours: true);

    expect($result->format('Y-m-d H:i'))->toBe('2026-07-24 00:05');
    Prompt::assertStrippedOutputContains('12:05 AM');
});

it('validates 12 hour datetime edits against full instant bounds', function () {
    Prompt::fake([Key::TAB, Key::TAB, Key::TAB, '09:30 AM', Key::ENTER, 'p', Key::ENTER]);

    $result = datetimepicker(
        'Release',
        default: '2026-07-24 14:30',
        min: '2026-07-24 12:00',
        max: '2026-07-24 22:00',
        use12Hours: true,
    );

    expect($result->format('Y-m-d H:i'))->toBe('2026-07-24 21:30');
    Prompt::assertOutputContains('Must be on or after 2026-07-24 12:00 PM.');
});

it('pastes a full 12 hour datetime including seconds', function () {
    Prompt::fake(['2027-12-25 12:05:45 AM', Key::ENTER]);

    $result = datetimepicker('Release', default: '2026-07-24 14:30:10', withSeconds: true, use12Hours: true);

    expect($result->format('Y-m-d H:i:s'))->toBe('2027-12-25 00:05:45');
    Prompt::assertStrippedOutputContains('2027-12-25 12:05:45 AM');
});

it('preserves the period when pasting datetime seconds at either precision', function ($period, $withSeconds, $expected) {
    Prompt::fake(["2026-07-24 09:45:12 {$period}", Key::ENTER]);

    $result = datetimepicker('Release', default: $period === 'PM' ? '2026-07-24 02:30' : '2026-07-24 14:30', use12Hours: true, withSeconds: $withSeconds);

    expect($result->format('Y-m-d H:i:s'))->toBe('2026-07-24 '.$expected);
})->with([
    ['AM', false, '09:45:00'],
    ['PM', false, '21:45:00'],
    ['AM', true, '09:45:12'],
    ['PM', true, '21:45:12'],
]);

it('renders a single inline datetime input with the weekday', function () {
    Prompt::fake([Key::ENTER]);

    datetimepicker(label: 'Release', default: '2026-07-24 14:30');

    Prompt::assertStrippedOutputContains('2026-07-24 14:30');
    Prompt::assertStrippedOutputContains('Friday');
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

it('validates inline time edits against the full instant', function () {
    Prompt::fake([Key::SHIFT_TAB, '15', Key::ENTER, Key::BACKSPACE, Key::BACKSPACE, '25', Key::ENTER]);

    $result = datetimepicker(label: 'Release', default: '2026-07-24 09:30', min: '2026-07-24 09:20');

    expect($result->format('Y-m-d H:i'))->toBe('2026-07-24 09:25');
    Prompt::assertOutputContains('Must be on or after 2026-07-24 09:20.');
});

it('rejects invalid time segments without normalizing them', function ($withSeconds, $keys, $typed, $correction) {
    Prompt::fake([...$keys, $typed, Key::ENTER, Key::BACKSPACE, Key::BACKSPACE, $correction, Key::ENTER]);

    $result = datetimepicker(label: 'Release', default: '2026-07-24 14:30:10', withSeconds: $withSeconds);

    expect($result->format('Y-m-d'))->toBe('2026-07-24');
    Prompt::assertOutputContains('Invalid time.');
})->with([
    'hour' => [false, [Key::TAB, Key::TAB, Key::TAB], '24', '23'],
    'minute' => [false, [Key::TAB, Key::TAB, Key::TAB, Key::TAB], '60', '59'],
    'second' => [true, [Key::SHIFT_TAB], '60', '59'],
]);

it('clamps datetime date stepping to the full instant boundaries', function () {
    Prompt::fake([Key::TAB, Key::TAB, Key::DOWN, Key::ENTER]);

    $result = datetimepicker(label: 'Release', default: '2026-07-25 09:30', min: '2026-07-24 10:00');

    expect($result->format('Y-m-d H:i'))->toBe('2026-07-24 10:00');
});

it('wraps inline seconds and tabs back to the year', function () {
    Prompt::fake([Key::SHIFT_TAB, Key::DOWN, Key::TAB, Key::UP, Key::ENTER]);

    $result = datetimepicker(label: 'Release', default: '2026-07-24 14:30:00', withSeconds: true);

    expect($result->format('Y-m-d H:i:s'))->toBe('2027-07-24 14:30:59');
});

it('preserves datetime hints', function () {
    Prompt::fake([Key::ENTER]);
    datetimepicker(label: 'Release', default: '2026-07-24 14:30');
    Prompt::assertStrippedOutputContains('Left/Right or Tab: move.');

    Prompt::fake([Key::ENTER]);
    datetimepicker(label: 'Release', default: '2026-07-24 14:30', hint: 'Custom hint.');
    Prompt::assertStrippedOutputContains('Custom hint.');

    Prompt::fake([Key::ENTER]);
    datetimepicker(label: 'Release', default: '2026-07-24 14:30', hint: '');
    Prompt::assertStrippedOutputDoesntContain('Left/Right or Tab');
});

it('allows valid bounded times to be entered across multiple segments', function () {
    Prompt::fake([Key::TAB, Key::TAB, Key::TAB, '10', Key::RIGHT, '00', Key::ENTER]);

    $prompt = new DateTimePickerPrompt(
        'Release',
        default: '2026-07-24 09:30',
        min: '2026-07-24 09:30',
        max: '2026-07-24 10:15',
    );
    $result = $prompt->prompt();

    expect($prompt->state)->toBe('submit')
        ->and($result->format('Y-m-d H:i'))->toBe('2026-07-24 10:00');
});

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

it('validates and transforms a date corrected through its time', function () {
    Prompt::fake([Key::TAB, Key::TAB, '24', Key::ENTER, '10', Key::ENTER]);

    $validated = null;
    $prompt = new DateTimePickerPrompt(
        'Release',
        default: '2026-07-25 09:30',
        min: '2026-07-24 10:00',
        max: '2026-07-25 09:45',
        transform: fn (DateTimeImmutable $value) => $value->format('Y-m-d H:i'),
        validate: function ($value) use (&$validated) {
            $validated = $value;
        },
    );
    $result = $prompt->prompt();

    expect($prompt->state)->toBe('submit')
        ->and($result)->toBe('2026-07-24 10:30')
        ->and($validated)->toBe($result);
    Prompt::assertOutputContains('Must be on or after 2026-07-24 10:00.');
});

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
    Prompt::fake([Key::TAB, Key::TAB, Key::TAB, Key::UP, Key::ENTER]);

    $result = datetimepicker(label: 'When should the deploy run?', default: '2026-07-24 14:30');

    expect($result->format('Y-m-d H:i'))->toBe('2026-07-24 15:30');
});

it('wraps the hour around midnight', function () {
    Prompt::fake([Key::TAB, Key::TAB, Key::TAB, Key::UP, Key::ENTER]);

    $result = datetimepicker(label: 'When should the deploy run?', default: '2026-07-24 23:30');

    expect($result->format('H:i'))->toBe('00:30');

    Prompt::fake([Key::TAB, Key::TAB, Key::TAB, Key::DOWN, Key::ENTER]);

    $result = datetimepicker(label: 'When should the deploy run?', default: '2026-07-24 00:15');

    expect($result->format('H:i'))->toBe('23:15');
});

it('replaces the selected time segment with typed digits', function () {
    Prompt::fake([Key::TAB, Key::TAB, Key::TAB, '0', '9', Key::ENTER]);

    $result = datetimepicker(label: 'When should the deploy run?', default: '2026-07-24 14:30');

    expect($result->format('H:i'))->toBe('09:30');

    Prompt::fake([Key::TAB, Key::TAB, Key::TAB, Key::TAB, '3', '7', Key::ENTER]);

    $result = datetimepicker(label: 'When should the deploy run?', default: '2026-07-24 14:59');

    expect($result->format('H:i'))->toBe('14:37');
});

it('ignores escape sequences while a time segment is focused', function () {
    Prompt::fake([Key::TAB, Key::TAB, Key::TAB, Key::PAGE_UP, Key::SHIFT_UP, Key::ENTER]);

    $result = datetimepicker(label: 'When should the deploy run?', default: '2026-07-24 14:30');

    expect($result->format('Y-m-d H:i'))->toBe('2026-07-24 14:30');
});

it('moves between time segments with the arrow keys', function () {
    Prompt::fake([Key::TAB, Key::TAB, Key::TAB, Key::RIGHT, '4', '5', Key::ENTER]);

    $result = datetimepicker(label: 'When should the deploy run?', default: '2026-07-24 14:30');

    expect($result->format('H:i'))->toBe('14:45');
});

it('wraps to the last time segment with shift tab', function () {
    Prompt::fake([Key::SHIFT_TAB, '4', '5', Key::ENTER]);

    $result = datetimepicker(label: 'When should the deploy run?', default: '2026-07-24 14:30');

    expect($result->format('H:i'))->toBe('14:45');
});

it('supports a seconds segment', function () {
    Prompt::fake([Key::TAB, Key::TAB, Key::TAB, Key::TAB, Key::TAB, Key::UP, Key::ENTER]);

    $result = datetimepicker(
        label: 'When should the deploy run?',
        default: '2026-07-24 14:30:10',
        withSeconds: true,
    );

    expect($result->format('Y-m-d H:i:s'))->toBe('2026-07-24 14:30:11');
});

it('highlights the focused time segment', function () {
    Prompt::fake([Key::TAB, Key::TAB, Key::TAB, Key::ENTER]);

    datetimepicker(label: 'When should the deploy run?', default: '2026-07-24 14:30');

    Prompt::assertStrippedOutputContains('2026-07-24 14:30');
    Prompt::assertOutputContains("\e[7m14\e[27m");
});

it('clamps stepped times to the range', function () {
    Prompt::fake([Key::TAB, Key::TAB, Key::TAB, Key::DOWN, Key::ENTER]);

    $result = datetimepicker(
        label: 'When should the deploy run?',
        default: '2026-07-24 09:30',
        min: '2026-07-24 09:00',
    );

    expect($result->format('Y-m-d H:i'))->toBe('2026-07-24 09:00');
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

it('rejects typed times in a daylight saving gap', function ($use12Hours, $default, $keys, $focused, $buffer) {
    Prompt::fake([...$keys, Key::ENTER, Key::CTRL_C]);

    $prompt = new DateTimePickerPrompt('Release', default: new DateTimeImmutable($default, new DateTimeZone('America/New_York')), use12Hours: $use12Hours);
    $prompt->prompt();

    expect($prompt->state)->toBe('cancel')
        ->and($prompt->focused)->toBe($focused)
        ->and($prompt->segmentBuffer)->toBe($buffer)
        ->and($prompt->value()->format('H:i'))->not->toBe('03:15');
    Prompt::assertOutputContains('Invalid time.');
})->with([
    '24h hour' => [false, '2026-03-08 01:30', [Key::TAB, Key::TAB, Key::TAB, '02:15'], 'hour', '02'],
    '12h period' => [true, '2026-03-08 14:15', [Key::SHIFT_TAB, 'a'], 'period', 'AM'],
]);

it('does not step into a daylight saving gap', function ($use12Hours, $default, $keys) {
    Prompt::fake([...$keys, Key::ENTER]);

    $result = datetimepicker('Release', default: new DateTimeImmutable($default, new DateTimeZone('America/New_York')), use12Hours: $use12Hours);

    expect($result->format('Y-m-d H:i'))->toBe($default);
    Prompt::assertOutputContains('Invalid time.');
})->with([
    '24h hour' => [false, '2026-03-08 01:30', [Key::TAB, Key::TAB, Key::TAB, Key::UP]],
    '12h hour' => [true, '2026-03-08 01:30', [Key::TAB, Key::TAB, Key::TAB, Key::UP]],
    '12h period' => [true, '2026-03-08 14:15', [Key::SHIFT_TAB, Key::UP]],
]);

it('shows the shifted time when stepping the date onto a daylight saving gap', function () {
    Prompt::fake([Key::TAB, Key::TAB, Key::UP, Key::ENTER]);

    $result = datetimepicker('Release', default: new DateTimeImmutable('2026-03-07 02:30', new DateTimeZone('America/New_York')));

    expect($result->format('Y-m-d H:i'))->toBe('2026-03-08 03:30');
    Prompt::assertStrippedOutputContains('2026-03-08 03:30');
});

it('clamps datetime time stepping to the full instant boundaries', function ($keys, $expected) {
    Prompt::fake([...$keys, Key::CTRL_C]);

    $prompt = new DateTimePickerPrompt('Release', default: '2026-07-24 12:30', min: '2026-07-24 12:30', max: '2026-07-24 13:00');
    $prompt->prompt();

    expect($prompt->value()->format('Y-m-d H:i'))->toBe($expected);
})->with([
    'hour below min' => [[Key::TAB, Key::TAB, Key::TAB, Key::DOWN], '2026-07-24 12:30'],
    'minute below min' => [[Key::TAB, Key::TAB, Key::TAB, Key::TAB, Key::DOWN], '2026-07-24 12:30'],
    'hour above max' => [[Key::TAB, Key::TAB, Key::TAB, Key::UP], '2026-07-24 13:00'],
]);
