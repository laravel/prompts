<?php

use Laravel\Prompts\P;

require __DIR__.'/../vendor/autoload.php';

$confirmed = P::confirm(
    label: 'Would you like to install dependencies?',
    validate: fn ($value) => match ($value) {
        false => 'You must install dependencies.',
        default => null,
    }
);

var_dump($confirmed);

echo str_repeat(PHP_EOL, 2);
