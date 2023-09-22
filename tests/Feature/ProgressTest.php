<?php

use Laravel\Prompts\Prompt;

use function Laravel\Prompts\progress;

it('renders a progress bar', function () {
    Prompt::fake();

    $states = [
        'Alabama', 'Alaska', 'Arizona', 'Arkansas', 'California', 'Colorado',
    ];

    progress(
        label: 'Adding States',
        items: $states,
        callback: fn () => usleep(1000),
    );

    Prompt::assertOutputContains('Adding States');
    Prompt::assertOutputDoesntContain('Alabama');
});

it('renders a progress bar with an item label', function () {
    Prompt::fake();

    $states = [
        'Alabama', 'Alaska', 'Arizona', 'Arkansas', 'California', 'Colorado',
    ];

    progress(
        label: 'Adding States',
        items: $states,
        callback: function ($item) {
            usleep(1000);

            return $item;
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
        items: $states,
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
        items: $states,
    );

    $progress->start();

    foreach ($states as $state) {
        usleep(1000);
        $progress->advance($state);
    }

    $progress->finish();

    Prompt::assertOutputContains('Adding States');

    foreach ($states as $state) {
        Prompt::assertOutputContains($state);
    }
});
