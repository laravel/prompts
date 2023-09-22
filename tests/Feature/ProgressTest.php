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
     └──────────────────────────────────────────────────────────────┘
    OUTPUT);

    Prompt::assertStrippedOutputContains('│ ▆▆▆▆▆▆▆▆▆▆▆▆▆▆▆                                              │');
    Prompt::assertStrippedOutputContains('│ ▆▆▆▆▆▆▆▆▆▆▆▆▆▆▆▆▆▆▆▆▆▆▆▆▆▆▆▆▆▆                               │');
    Prompt::assertStrippedOutputContains('│ ▆▆▆▆▆▆▆▆▆▆▆▆▆▆▆▆▆▆▆▆▆▆▆▆▆▆▆▆▆▆▆▆▆▆▆▆▆▆▆▆▆▆▆▆▆▆▆▆▆▆▆▆▆▆▆▆▆▆▆▆ │');

    Prompt::assertStrippedOutputContains(<<<'OUTPUT'
     ┌ Adding States ───────────────────────────────────────────────┐
     │ ▆▆▆▆▆▆▆▆▆▆▆▆▆▆▆▆▆▆▆▆▆▆▆▆▆▆▆▆▆▆▆▆▆▆▆▆▆▆▆▆▆▆▆▆▆▆▆▆▆▆▆▆▆▆▆▆▆▆▆▆ │
     └──────────────────────────────────────────────────────────────┘
    OUTPUT);
})->with([
    'array' => [['Alabama', 'Alaska', 'Arizona', 'Arkansas']],
    'collection' => [collect(['Alabama', 'Alaska', 'Arizona', 'Arkansas'])],
    'integer' => [4],
]);

it('renders a progress bar with an item label', function () {
    Prompt::fake();

    $states = [
        'Alabama', 'Alaska', 'Arizona', 'Arkansas', 'California', 'Colorado',
    ];

    progress(
        label: 'Adding States',
        steps: $states,
        callback: function ($item, $progress) {
            usleep(1000);
            $progress->itemLabel = $item;
        }
    );

    Prompt::assertOutputContains('Adding States');

    foreach ($states as $state) {
        Prompt::assertOutputContains($state);
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

it('can provide an item label when in manual mode', function () {
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
        $progress->itemLabel = $state;
        $progress->advance();
    }

    $progress->finish();

    Prompt::assertOutputContains('Adding States');

    foreach ($states as $state) {
        Prompt::assertOutputContains($state);
    }
});
