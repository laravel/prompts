<?php

use function Laravel\Prompts\number;

require __DIR__.'/../vendor/autoload.php';

$value = number(
    label: 'How many items do you want to buy?',
    placeholder: 'E.g. 10',
    validate: fn ($value) => match (true) {
        $value !== 6 => 'Actually you have to buy 6 items.',
        default => null,
    },
    hint: 'You can buy up to 10 items.',
    min: 1,
    max: 10,
);

var_dump($value, gettype($value));

echo str_repeat(PHP_EOL, 5);
