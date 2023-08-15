<?php

use function Laravel\Prompts\confirm;

require __DIR__.'/../vendor/autoload.php';

$confirmed = confirm(
    label: 'Would you like to install dependencies?',
    validate: fn ($value) => match ($value) {
        false => 'You must install dependencies.',
        default => null,
    },
    hint: 'Dependencies are required to run the application.',
);

var_dump($confirmed);

echo str_repeat(PHP_EOL, 2);
