<?php

use function Laravel\Prompts\progress;

require __DIR__ . '/../vendor/autoload.php';

$states = [
    'Alabama', 'Alaska', 'Arizona', 'Arkansas', 'California', 'Colorado',
    'Connecticut', 'Delaware', 'Florida', 'Georgia', 'Hawaii', 'Idaho',
];

progress(
    label: 'Adding States',
    items: $states,
    callback: function ($item) {
        usleep(250_000);
    },
);

progress(
    label: 'Adding States With Label',
    items: $states,
    callback: function ($item) {
        usleep(250_000);
        return $item;
    },
);

$progressBar = progress(
    label: 'Adding States Manually',
    items: $states,
);

$progressBar->start();

foreach ($states as $state) {
    usleep(250_000);
    $progressBar->advance($state);
}

$progressBar->finish();

progress(
    'Processing with Exception',
    $states,
    fn ($item) => $item === 'Arkansas' ? throw new Exception('Issue with Arkansas!') : usleep(250_000),
);

echo str_repeat(PHP_EOL, 6);
