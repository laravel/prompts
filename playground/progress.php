<?php

use function Laravel\Prompts\progress;

require __DIR__ . '/../vendor/autoload.php';

$states = [
    'Alabama', 'Alaska', 'Arizona', 'Arkansas', 'California', 'Colorado',
    'Connecticut', 'Delaware', 'Florida', 'Georgia', 'Hawaii', 'Idaho',
];

progress(
    label: 'Adding States',
    steps: $states,
    callback: function ($item) {
        usleep(250_000);
    },
);

progress(
    label: 'Adding States With Label',
    steps: $states,
    callback: function ($item, $progress) {
        usleep(250_000);
        $progress->itemLabel = $item;
    },
);

$progress = progress(
    label: 'Adding States Manually',
    steps: $states,
);

$progress->start();

foreach ($states as $state) {
    usleep(250_000);
    $progress->itemLabel = $state;
    $progress->advance();
}

$progress->finish();

progress(
    'Processing with Exception',
    $states,
    fn ($item) => $item === 'Arkansas' ? throw new Exception('Issue with Arkansas!') : usleep(250_000),
);
