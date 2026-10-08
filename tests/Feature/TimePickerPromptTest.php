<?php

use Laravel\Prompts\Exceptions\NonInteractiveValidationException;
use Laravel\Prompts\Key;
use Laravel\Prompts\Prompt;
use Laravel\Prompts\TimePickerPrompt;

use function Laravel\Prompts\timepicker;

it('renders an inline 24 hour time by default', function () {
    Prompt::fake([Key::ENTER]);

    $result = timepicker('Start time', default: '14:30:45');

    expect($result)->toBeInstanceOf(DateTimeImmutable::class)
        ->and($result->format('H:i:s'))->toBe('14:30:00');
    Prompt::assertStrippedOutputContains('14:30');
    Prompt::assertStrippedOutputDoesntContain('PM');
});

it('edits and wraps time segments without carrying', function ($keys, $expected) {
    Prompt::fake([...$keys, Key::ENTER]);

    $result = timepicker('Start time', default: '23:00:00', withSeconds: true);

    expect($result->format('H:i:s'))->toBe($expected);
})->with([
    'hour' => [[Key::UP], '00:00:00'],
    'minute' => [[Key::RIGHT, Key::DOWN], '23:59:00'],
    'second' => [[Key::SHIFT_TAB, Key::DOWN], '23:00:59'],
    'paste' => [['09:45:12'], '09:45:12'],
]);

it('renders midnight and noon in 12 hour mode', function ($default, $display) {
    Prompt::fake([Key::ENTER]);

    timepicker('Start time', default: $default, use12Hours: true);

    Prompt::assertStrippedOutputContains($display);
})->with([
    ['00:00', '12:00 AM'],
    ['12:00', '12:00 PM'],
    ['23:30', '11:30 PM'],
]);

it('edits 12 hour times and their period', function ($keys, $expected) {
    Prompt::fake([...$keys, Key::ENTER]);

    $result = timepicker('Start time', default: '14:30', use12Hours: true);

    expect($result->format('H:i'))->toBe($expected);
})->with([
    'paste AM' => [['12:05 am'], '00:05'],
    'paste PM' => [['01:45 PM'], '13:45'],
    'toggle' => [[Key::SHIFT_TAB, Key::UP], '02:30'],
    'type period' => [[Key::SHIFT_TAB, 'a'], '02:30'],
    'correct period' => [[Key::SHIFT_TAB, Key::BACKSPACE, 'm'], '14:30'],
    'replace incomplete period' => [[Key::SHIFT_TAB, Key::BACKSPACE, 'a'], '02:30'],
    'hour wraps within period' => [['12', Key::UP], '13:30'],
]);

it('rejects invalid typed hours before advancing', function ($use12Hours, $input, $correction) {
    Prompt::fake([$input, Key::TAB, Key::ENTER, Key::BACKSPACE, Key::BACKSPACE, $correction, Key::ENTER]);

    $result = timepicker('Start time', default: '14:30', use12Hours: $use12Hours);

    expect($result->format('H:i'))->toBe('14:30');
    Prompt::assertOutputContains('Invalid time.');
})->with([[false, '24', '14'], [true, '00', '02'], [true, '13', '02']]);

it('stops malformed pasted input at the invalid segment', function () {
    Prompt::fake(['24:01', Key::ENTER, Key::CTRL_C]);

    $prompt = new TimePickerPrompt('Start time', default: '14:30');
    $prompt->prompt();

    expect($prompt->state)->toBe('cancel')->and($prompt->segmentBuffer)->toBe('24');
});

it('validates clock time bounds independently of the supplied dates', function () {
    Prompt::fake(['10:00', Key::ENTER]);

    $result = timepicker('Start time', default: '2026-07-24 09:30', min: '2020-01-01 09:30', max: '2030-01-01 10:15');

    expect($result->format('Y-m-d H:i'))->toBe('2026-07-24 10:00');
});

it('rejects typed times outside the range', function () {
    Prompt::fake(['08:00', Key::ENTER, Key::LEFT, '09', Key::ENTER]);

    $result = timepicker('Start time', default: '09:30', min: '09:00');

    expect($result->format('H:i'))->toBe('09:00');
    Prompt::assertOutputContains('Must be on or after 09:00.');
});

it('preserves the default date and timezone', function () {
    Prompt::fake(['09:45', Key::ENTER]);

    $result = timepicker('Start time', default: new DateTimeImmutable('2026-07-24 14:30', new DateTimeZone('America/New_York')));

    expect($result->format('Y-m-d H:i e'))->toBe('2026-07-24 09:45 America/New_York');
});

it('validates and transforms a committed time', function () {
    Prompt::fake(['09:45', Key::ENTER]);

    $validated = null;
    $result = timepicker('Start time', default: '14:30', transform: fn ($value) => $value->format('H:i'), validate: function ($value) use (&$validated) {
        $validated = $value;
    });

    expect($result)->toBe('09:45')->and($validated)->toBe($result);
});

it('returns the default or null when non-interactive', function () {
    Prompt::interactive(false);

    expect(timepicker('Start time', default: '14:30')->format('H:i'))->toBe('14:30')
        ->and(timepicker('Start time'))->toBeNull();
});

it('rejects required missing non-interactive times', function () {
    Prompt::interactive(false);

    timepicker('Start time', required: true);
})->throws(NonInteractiveValidationException::class, 'Required.');

it('rejects reversed clock time bounds', function () {
    timepicker('Start time', min: '22:00', max: '06:00');
})->throws(InvalidArgumentException::class, 'min');

it('rejects invalid time defaults', function ($default) {
    timepicker('Start time', default: $default);
})->with(['not-a-time', '25:00', '24:00'])->throws(InvalidArgumentException::class, 'Time');

it('rejects non-interactive defaults outside the clock time range', function () {
    Prompt::interactive(false);

    timepicker('Start time', default: '08:00', min: '09:00');
})->throws(NonInteractiveValidationException::class, 'Must be on or after 09:00.');

it('clamps stepping and defaults to clock time bounds', function () {
    Prompt::fake([Key::UP, Key::ENTER]);

    $result = timepicker('Start time', default: '2026-07-24 11:00', max: '10:15');

    expect($result->format('Y-m-d H:i'))->toBe('2026-07-24 10:15');
});

it('preserves hints and allows an empty hint', function () {
    Prompt::fake([Key::ENTER]);
    timepicker('Start time', default: '14:30', hint: 'Choose a time.');
    Prompt::assertStrippedOutputContains('Choose a time.');

    Prompt::fake([Key::ENTER]);
    timepicker('Start time', default: '14:30', hint: '');
    Prompt::assertStrippedOutputDoesntContain('Left/Right or Tab');
});

it('does not submit an incomplete time segment', function () {
    Prompt::fake([Key::BACKSPACE, Key::BACKSPACE, Key::ENTER, '09', Key::ENTER]);

    $result = timepicker('Start time', default: '14:30');

    expect($result->format('H:i'))->toBe('09:30');
    Prompt::assertOutputContains('Incomplete hour.');
});

it('rejects invalid minute and second segments', function ($keys) {
    Prompt::fake([...$keys, '60', Key::ENTER, Key::BACKSPACE, Key::BACKSPACE, '59', Key::ENTER]);

    timepicker('Start time', default: '14:30:10', withSeconds: true);

    Prompt::assertOutputContains('Invalid time.');
})->with([
    'minute' => [[Key::TAB]],
    'second' => [[Key::SHIFT_TAB]],
]);

it('ignores escape sequences while editing a time', function () {
    Prompt::fake([Key::DELETE, "\e[1;5C", Key::ENTER]);

    expect(timepicker('Start time', default: '14:30')->format('H:i'))->toBe('14:30');
});

it('can cancel a timepicker', function () {
    Prompt::fake([Key::CTRL_C]);

    timepicker('Start time', default: '14:30');

    Prompt::assertOutputContains('Cancelled.');
});

it('can fall back', function () {
    Prompt::fallbackWhen(true);
    TimePickerPrompt::fallbackUsing(fn (TimePickerPrompt $prompt) => new DateTimeImmutable('09:45'));

    expect(timepicker('Start time')->format('H:i'))->toBe('09:45');
});

it('exposes the original validation rules to custom validators', function () {
    Prompt::validateUsing(function (Prompt $prompt) {
        expect($prompt->validate)->toBe('business-hours');

        return $prompt->value()->format('H') < 9 ? 'Choose business hours.' : null;
    });
    Prompt::fake([Key::ENTER, '09', Key::ENTER]);

    expect(timepicker('Start time', default: '08:00', validate: 'business-hours')->format('H:i'))->toBe('09:00');
    Prompt::assertOutputContains('Choose business hours.');
    Prompt::validateUsing(fn () => null);
});

it('renders and edits 12 hour seconds', function () {
    Prompt::fake(['12:05:45 AM', Key::ENTER]);

    $result = timepicker('Start time', default: '14:30:10', withSeconds: true, use12Hours: true);

    expect($result->format('H:i:s'))->toBe('00:05:45');
    Prompt::assertStrippedOutputContains('12:05:45 AM');
});

it('validates 12 hour bounds using 24 hour clock values', function () {
    Prompt::fake(['09:00 AM', Key::ENTER, 'p', Key::ENTER]);

    $result = timepicker('Start time', default: '14:30', min: '12:00', max: '22:00', use12Hours: true);

    expect($result->format('H:i'))->toBe('21:00');
    Prompt::assertOutputContains('Must be on or after 12:00 PM.');
});

it('stops input after the final time segment', function () {
    Prompt::fake(['09:45:59', Key::ENTER]);

    expect(timepicker('Start time', default: '14:30')->format('H:i:s'))->toBe('09:45:00');
});
