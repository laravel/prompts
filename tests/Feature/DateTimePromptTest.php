<?php

use Laravel\Prompts\Key;
use Laravel\Prompts\Prompt;

use function Laravel\Prompts\datetime;

it('returns the default datetime with the seconds zeroed', function () {
    Prompt::fake([Key::ENTER]);

    $result = datetime(
        label: 'When should the deploy run?',
        default: '2026-07-24 14:30:45',
    );

    expect($result)->toBeInstanceOf(DateTimeImmutable::class)
        ->and($result->format('Y-m-d H:i:s'))->toBe('2026-07-24 14:30:00');
});

it('edits the hour with the arrow keys after tabbing', function () {
    Prompt::fake([Key::TAB, Key::UP, Key::ENTER]);

    $result = datetime(
        label: 'When should the deploy run?',
        default: '2026-07-24 14:30',
    );

    expect($result->format('Y-m-d H:i'))->toBe('2026-07-24 15:30');
});

it('wraps the hour around midnight', function () {
    Prompt::fake([Key::TAB, Key::UP, Key::ENTER]);

    $result = datetime(
        label: 'When should the deploy run?',
        default: '2026-07-24 23:30',
    );

    expect($result->format('H:i'))->toBe('00:30');

    Prompt::fake([Key::TAB, Key::DOWN, Key::ENTER]);

    $result = datetime(
        label: 'When should the deploy run?',
        default: '2026-07-24 00:15',
    );

    expect($result->format('H:i'))->toBe('23:15');
});

it('types into the focused time segment', function () {
    Prompt::fake([Key::TAB, Key::TAB, '4', '5', Key::ENTER]);

    $result = datetime(
        label: 'When should the deploy run?',
        default: '2026-07-24 14:30',
    );

    expect($result->format('H:i'))->toBe('14:45');
});

it('ignores escape sequences while a time segment is focused', function () {
    Prompt::fake([Key::TAB, Key::PAGE_UP, Key::SHIFT_UP, Key::ENTER]);

    $result = datetime(
        label: 'When should the deploy run?',
        default: '2026-07-24 14:30',
    );

    expect($result->format('Y-m-d H:i'))->toBe('2026-07-24 14:30');
});

it('moves between time segments with the arrow keys', function () {
    Prompt::fake([Key::TAB, Key::RIGHT, '4', '5', Key::ENTER]);

    $result = datetime(
        label: 'When should the deploy run?',
        default: '2026-07-24 14:30',
    );

    expect($result->format('H:i'))->toBe('14:45');
});

it('cycles the focus back to the calendar with tab', function () {
    Prompt::fake([Key::TAB, Key::TAB, Key::TAB, Key::RIGHT, Key::ENTER]);

    $result = datetime(
        label: 'When should the deploy run?',
        default: '2026-07-24 14:30',
    );

    expect($result->format('Y-m-d H:i'))->toBe('2026-07-25 14:30');
});

it('cycles the focus backwards with shift tab', function () {
    Prompt::fake([Key::SHIFT_TAB, '4', '5', Key::ENTER]);

    $result = datetime(
        label: 'When should the deploy run?',
        default: '2026-07-24 14:30',
    );

    expect($result->format('H:i'))->toBe('14:45');
});

it('returns to the calendar with the left arrow from the hour', function () {
    Prompt::fake([Key::TAB, Key::LEFT, Key::DOWN, Key::ENTER]);

    $result = datetime(
        label: 'When should the deploy run?',
        default: '2026-07-24 14:30',
    );

    expect($result->format('Y-m-d H:i'))->toBe('2026-07-31 14:30');
});

it('supports a seconds segment', function () {
    Prompt::fake([Key::TAB, Key::TAB, Key::TAB, Key::UP, Key::ENTER]);

    $result = datetime(
        label: 'When should the deploy run?',
        default: '2026-07-24 14:30:10',
        withSeconds: true,
    );

    expect($result->format('Y-m-d H:i:s'))->toBe('2026-07-24 14:30:11');
});

it('still accepts typed dates while the calendar is focused', function () {
    Prompt::fake(['2', '0', '2', '6', '1', '2', '2', '5', Key::ENTER]);

    $result = datetime(
        label: 'When should the deploy run?',
        default: '2026-07-24 14:30',
    );

    expect($result->format('Y-m-d H:i'))->toBe('2026-12-25 14:30');
});

it('renders the time row and highlights the focused segment', function () {
    Prompt::fake([Key::TAB, Key::ENTER]);

    datetime(label: 'When should the deploy run?', default: '2026-07-24 14:30');

    Prompt::assertStrippedOutputContains('Time  14:30');
    Prompt::assertStrippedOutputContains('2026-07-24 14:30');
    Prompt::assertOutputContains("\e[7m14\e[27m");
});

it('rejects times outside of the range', function () {
    Prompt::fake([Key::TAB, Key::DOWN, Key::ENTER, Key::UP, Key::ENTER]);

    $result = datetime(
        label: 'When should the deploy run?',
        default: '2026-07-24 09:30',
        min: '2026-07-24 09:00',
    );

    expect($result->format('Y-m-d H:i'))->toBe('2026-07-24 09:30');

    Prompt::assertOutputContains('Must be on or after 2026-07-24 09:00.');
});

it('returns the default when non-interactive', function () {
    Prompt::interactive(false);

    $result = datetime(label: 'When should the deploy run?', default: '2026-07-24 14:30');

    expect($result->format('Y-m-d H:i'))->toBe('2026-07-24 14:30');
});

it('returns null when non-interactive without a default', function () {
    Prompt::interactive(false);

    expect(datetime(label: 'When should the deploy run?'))->toBeNull();
});
