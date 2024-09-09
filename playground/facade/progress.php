<?php

use Laravel\Prompts\Support\Facades\Prompt;

require __DIR__ . '/../../vendor/autoload.php';

$states = [
    'Alabama',
    'Alaska',
    'Arizona',
    'Arkansas',
    'California',
    'Colorado',
    'Connecticut',
    'Delaware',
    'Florida',
    'Georgia',
    'Hawaii',
    'Idaho',
];

Prompt::progress(
    label: 'Adding States',
    steps: $states,
    callback: function ($item, $progress) {
        usleep(250_000);

        if ($item === 'Arkansas') {
            $progress->label = 'Arkansas is not a state! Nice try.';
        }

        return $item . ' added.';
    },
);

Prompt::progress(
    label: 'Adding States With Label',
    steps: $states,
    callback: function ($item, $progress) {
        usleep(250_000);
        $progress
            ->label('Adding ' . $item)
            ->hint("{$item} has " . strlen($item) . ' characters');
    },
);

$progress = Prompt::progress(
    label: 'Adding States Manually',
    steps: $states,
);

$progress->start();

foreach ($states as $state) {
    usleep(250_000);
    $progress
        ->hint($state)
        ->advance();
}

$progress->finish();

Prompt::progress(
    'Processing with Exception',
    $states,
    fn($item) => $item === 'Arkansas' ? throw new Exception('Issue with Arkansas!') : usleep(250_000),
);
