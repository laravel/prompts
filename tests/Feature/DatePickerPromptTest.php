<?php

use Laravel\Prompts\DatePickerPrompt;
use Laravel\Prompts\Exceptions\NonInteractiveValidationException;
use Laravel\Prompts\Key;
use Laravel\Prompts\Prompt;

use function Laravel\Prompts\datepicker;

it('can use the datepicker alongside the built-in date function', function () {
    Prompt::fake([Key::ENTER]);

    $result = datepicker(label: 'Release date', default: '2026-07-24');

    expect($result->format('Y-m-d'))->toBe('2026-07-24')
        ->and(date('Y', 86400))->toBe('1970');
});

it('renders an inline date input by default', function () {
    Prompt::fake([Key::ENTER]);

    datepicker(label: 'Release date', default: '2026-07-24');

    Prompt::assertStrippedOutputContains('2026-07-24');
    Prompt::assertOutputContains("\e[7m2026\e[27m");
});

it('shows the weekday of the date being edited', function () {
    Prompt::fake([Key::TAB, Key::TAB, '2', '5', Key::CTRL_C]);

    $prompt = new DatePickerPrompt(label: 'Release date', default: '2026-07-24');
    $prompt->prompt();

    expect($prompt->date->format('Y-m-d'))->toBe('2026-07-24');
    Prompt::assertStrippedOutputContains('Friday');
    Prompt::assertStrippedOutputContains('Saturday');
});

it('moves inline focus without changing the date', function () {
    Prompt::fake([Key::LEFT, Key::RIGHT, Key::CTRL_F, Key::RIGHT, Key::ENTER]);

    $result = datepicker(label: 'Release date', default: '2026-07-24');

    expect($result->format('Y-m-d'))->toBe('2026-07-24');
    Prompt::assertOutputContains("\e[7m07\e[27m");
    Prompt::assertOutputContains("\e[7m24\e[27m");
});

it('steps inline date segments safely', function ($default, $keys, $expected) {
    Prompt::fake([...$keys, Key::ENTER]);

    $result = datepicker(label: 'Release date', default: $default);

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

    $result = datepicker(label: 'Release date', default: '2026-07-24');

    expect($result->format('Y-m-d'))->toBe('2027-12-25');
});

it('rejects invalid inline edits before moving focus or submitting', function ($keys, $correction, $error, $expected) {
    Prompt::fake([...$keys, Key::TAB, Key::ENTER, ...$correction, Key::ENTER]);

    $result = datepicker(label: 'Release date', default: '2026-02-24');

    expect($result->format('Y-m-d'))->toBe($expected);
    Prompt::assertOutputContains($error);
})->with([
    'incomplete year' => [['20'], ['26'], 'Incomplete year.', '2026-02-24'],
    'zero month' => [[Key::TAB, '00'], [Key::BACKSPACE, '2'], 'Invalid date.', '2026-02-24'],
    'impossible day' => [[Key::TAB, Key::TAB, '30'], [Key::BACKSPACE, Key::BACKSPACE, '01'], 'Invalid date.', '2026-02-01'],
]);

it('clamps inline stepping but rejects typed dates outside the range', function () {
    Prompt::fake([Key::SHIFT_TAB, Key::UP, '30', Key::ENTER, Key::BACKSPACE, Key::BACKSPACE, '05', Key::ENTER]);

    $result = datepicker(label: 'Release date', default: '2026-07-24', max: '2026-07-25');

    expect($result->format('Y-m-d'))->toBe('2026-07-05');
    Prompt::assertOutputContains('Must be on or before 2026-07-25.');
});

it('preserves the timezone when typing inline segments', function () {
    Prompt::fake(['2027-12-25', Key::ENTER]);

    $result = datepicker(label: 'Release date', default: new DateTimeImmutable('2026-07-24', new DateTimeZone('America/New_York')));

    expect($result->format('Y-m-d e'))->toBe('2027-12-25 America/New_York');
});

it('preserves custom and empty hints', function () {
    Prompt::fake([Key::ENTER]);
    datepicker(label: 'Release date', default: '2026-07-24', hint: 'A custom hint.');
    Prompt::assertStrippedOutputContains('A custom hint.');

    Prompt::fake([Key::ENTER]);
    datepicker(label: 'Release date', default: '2026-07-24', hint: '');
    Prompt::assertStrippedOutputDoesntContain('Left/Right or Tab');
});

it('ignores unrelated characters and escape sequences in inline segments', function () {
    Prompt::fake(['a!', Key::DELETE, "\e[1;5C", '2027', Key::ENTER]);

    $result = datepicker(label: 'Release date', default: '2026-07-24');

    expect($result->format('Y-m-d'))->toBe('2027-07-24');
});

it('validates and transforms the committed inline date', function () {
    Prompt::fake(['2027-12-25', Key::ENTER]);

    $validated = null;
    $result = datepicker(
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

    $result = datepicker(label: 'Release date', default: '2026-07-24');

    expect($result->format('Y-m-d'))->toBe('2026-07-25');
    Prompt::assertOutputContains('Incomplete day.');
});

it('allows valid bounded dates to be entered across multiple segments', function ($keys) {
    Prompt::fake([...$keys, Key::ENTER]);

    $prompt = new DatePickerPrompt('Release date', default: '2026-12-31', min: '2026-12-01', max: '2027-01-31');
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

    $prompt = new DatePickerPrompt('Release date', default: '2026-07-24');
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

    $result = datepicker(label: 'Release date', default: '2026-07-24');

    expect($result->format('Y-m-d'))->toBe('2027-12-25');
});

it('returns the default date as a DateTimeImmutable at midnight', function () {
    Prompt::fake([Key::ENTER]);

    $result = datepicker(
        label: 'When should the deploy run?',
        default: '2026-07-24',
    );

    expect($result)->toBeInstanceOf(DateTimeImmutable::class)
        ->and($result->format('Y-m-d H:i:s'))->toBe('2026-07-24 00:00:00');
});

it('accepts a DateTimeInterface default', function () {
    Prompt::fake([Key::ENTER]);

    $result = datepicker(
        label: 'When should the deploy run?',
        default: new DateTime('2026-07-24 15:30:00'),
    );

    expect($result->format('Y-m-d H:i:s'))->toBe('2026-07-24 00:00:00');
});

it('defaults to today', function () {
    Prompt::fake([Key::ENTER]);

    $result = datepicker(label: 'When should the deploy run?');

    expect($result->format('Y-m-d H:i:s'))
        ->toBe((new DateTimeImmutable('today'))->format('Y-m-d H:i:s'));
});

it('transforms values', function () {
    Prompt::fake([Key::ENTER]);

    $result = datepicker(
        label: 'When should the deploy run?',
        default: '2026-07-24',
        transform: fn (DateTimeImmutable $date) => $date->format('Y-m-d'),
    );

    expect($result)->toBe('2026-07-24');
});

it('validates', function () {
    Prompt::fake([Key::ENTER, Key::TAB, Key::TAB, Key::DOWN, Key::ENTER]);

    $result = datepicker(
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
    datepicker(label: 'When should the deploy run?', default: 'not-a-date');
})->throws(InvalidArgumentException::class, 'not-a-date');

it('can be cancelled', function () {
    Prompt::fake([Key::CTRL_C]);

    datepicker(label: 'When should the deploy run?', default: '2026-07-24');

    Prompt::assertOutputContains('Cancelled.');
});

it('returns the default when non-interactive', function () {
    Prompt::interactive(false);

    $result = datepicker(label: 'When should the deploy run?', default: '2026-07-24');

    expect($result->format('Y-m-d'))->toBe('2026-07-24');
});

it('returns null when non-interactive without a default', function () {
    Prompt::interactive(false);

    expect(datepicker(label: 'When should the deploy run?'))->toBeNull();
});

it('fails when non-interactive and required without a default', function () {
    Prompt::interactive(false);

    datepicker(label: 'When should the deploy run?', required: true);
})->throws(NonInteractiveValidationException::class, 'Required.');

it('fails when non-interactive with a default outside of the range', function () {
    Prompt::interactive(false);

    datepicker(label: 'When should the deploy run?', default: '2026-07-24', min: '2026-08-01');
})->throws(NonInteractiveValidationException::class, 'Must be on or after 2026-08-01.');

it('renders the submitted date', function () {
    Prompt::fake([Key::ENTER]);

    datepicker(label: 'When should the deploy run?', default: '2026-07-24');

    Prompt::assertStrippedOutputContains('2026-07-24');
});

it('ignores non-digit input', function () {
    Prompt::fake(['a', '!', Key::ENTER]);

    $result = datepicker(label: 'When should the deploy run?', default: '2026-07-24');

    expect($result->format('Y-m-d'))->toBe('2026-07-24');
});

it('clamps day stepping to the min date', function () {
    Prompt::fake([Key::TAB, Key::TAB, Key::DOWN, Key::DOWN, Key::ENTER]);

    $result = datepicker(
        label: 'When should the deploy run?',
        default: '2026-07-02',
        min: '2026-07-01',
    );

    expect($result->format('Y-m-d'))->toBe('2026-07-01');
});

it('clamps month stepping to the max date', function () {
    Prompt::fake([Key::TAB, Key::UP, Key::ENTER]);

    $result = datepicker(
        label: 'When should the deploy run?',
        default: '2026-07-24',
        max: '2026-08-05',
    );

    expect($result->format('Y-m-d'))->toBe('2026-08-05');
});

it('clamps the default into the range', function () {
    Prompt::fake([Key::ENTER]);

    $result = datepicker(
        label: 'When should the deploy run?',
        default: '2026-07-24',
        min: '2026-08-01',
    );

    expect($result->format('Y-m-d'))->toBe('2026-08-01');
});

it('rejects a min date after the max date', function () {
    datepicker(label: 'When should the deploy run?', min: '2026-07-31', max: '2026-07-01');
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

    Prompt::fake([Key::ENTER, Key::TAB, Key::TAB, Key::DOWN, Key::ENTER]);

    $result = datepicker(
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

    DatePickerPrompt::fallbackUsing(function (DatePickerPrompt $prompt) {
        expect($prompt->label)->toBe('When should the deploy run?');

        return new DateTimeImmutable('2026-01-01');
    });

    $result = datepicker(label: 'When should the deploy run?');

    expect($result->format('Y-m-d'))->toBe('2026-01-01');
});
