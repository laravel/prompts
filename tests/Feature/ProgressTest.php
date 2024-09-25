<?php

use Laravel\Prompts\Prompt;

use function Laravel\Prompts\progress;

it('renders a progress bar', function ($steps) {
    Prompt::fake();

    progress(
        label: 'Adding States',
        steps: $steps,
        callback: fn () => usleep(1000),
    );

    Prompt::assertStrippedOutputContains(<<<'OUTPUT'
     ┌ Adding States ───────────────────────────────────────────────┐
     │                                                              │
     └───────────────────────────────────────────────────────── 0/4 ┘
    OUTPUT);

    Prompt::assertStrippedOutputContains(<<<'OUTPUT'
     │ ███████████████                                              │
     └───────────────────────────────────────────────────────── 1/4 ┘
    OUTPUT);

    Prompt::assertStrippedOutputContains(<<<'OUTPUT'
     │ ██████████████████████████████                               │
     └───────────────────────────────────────────────────────── 2/4 ┘
    OUTPUT);

    Prompt::assertStrippedOutputContains(<<<'OUTPUT'
     │ █████████████████████████████████████████████                │
     └───────────────────────────────────────────────────────── 3/4 ┘
    OUTPUT);

    Prompt::assertStrippedOutputContains(<<<'OUTPUT'
     ┌ Adding States ───────────────────────────────────────────────┐
     │ ████████████████████████████████████████████████████████████ │
     └───────────────────────────────────────────────────────── 4/4 ┘
    OUTPUT);
})->with([
    'array' => [['Alabama', 'Alaska', 'Arizona', 'Arkansas']],
    'integer' => [4],
    ...depends_on_collection() ? ['collection' => [collect(['Alabama', 'Alaska', 'Arizona', 'Arkansas'])]] : [],
]);

it('renders a progress bar without a label', function () {
    Prompt::fake();

    progress(
        label: '',
        steps: 6,
        callback: function ($item, $progress) {
            usleep(1000);
            $progress->hint($item);
        }
    );

    Prompt::assertStrippedOutputContains(<<<'OUTPUT'
     ┌──────────────────────────────────────────────────────────────┐
     │                                                              │
     └───────────────────────────────────────────────────────── 0/6 ┘
    OUTPUT);
});

it('returns the results of the callback', function () {
    Prompt::fake();

    $result = progress(
        label: 'Uppercasing States',
        steps: ['Alabama', 'Alaska', 'Arizona', 'Arkansas'],
        callback: function ($item) {
            return strtoupper($item);
        }
    );

    expect($result)->toBe(['ALABAMA', 'ALASKA', 'ARIZONA', 'ARKANSAS']);
});

it('can update the label and hint while rendering', function () {
    Prompt::fake();

    $states = [
        'Alabama', 'Alaska', 'Arizona', 'Arkansas', 'California', 'Colorado',
    ];

    progress(
        label: 'Adding States',
        steps: $states,
        callback: function ($item, $progress) {
            usleep(1000);
            $progress->label(strtoupper($item));
            $progress->hint(strtolower($item));
        }
    );

    Prompt::assertOutputContains('Adding States');

    foreach ($states as $state) {
        Prompt::assertOutputContains(strtoupper($state));
        Prompt::assertOutputContains(strtolower($state));
    }
});

it('returns a manual progress bar when no callback is supplied', function () {
    Prompt::fake();

    $states = [
        'Alabama', 'Alaska', 'Arizona', 'Arkansas', 'California', 'Colorado',
    ];

    $progress = progress(
        label: 'Adding States',
        steps: count($states),
    );

    $progress->start();

    foreach ($states as $state) {
        usleep(1000);
        $progress->advance();
    }

    $progress->finish();

    Prompt::assertOutputContains('Adding States');
    Prompt::assertOutputDoesntContain('Alabama');
});

it('can update the label and hint in manual mode', function () {
    Prompt::fake();

    $states = [
        'Alabama', 'Alaska', 'Arizona', 'Arkansas', 'California', 'Colorado',
    ];

    $progress = progress(
        label: 'Adding States',
        steps: count($states),
    );

    $progress->start();

    foreach ($states as $state) {
        usleep(1000);
        $progress
            ->label(strtoupper($state))
            ->hint(strtolower($state))
            ->advance();
    }

    $progress->finish();

    Prompt::assertOutputContains('Adding States');

    foreach ($states as $state) {
        Prompt::assertOutputContains(strtoupper($state));
        Prompt::assertOutputContains(strtolower($state));
    }
});
