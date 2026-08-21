<?php

use function Laravel\Prompts\date;

require __DIR__.'/../vendor/autoload.php';

$date = date(
    label: 'When should the deploy run?',
    default: '+3 days',
    min: 'today',
    max: '+1 year',
    validate: fn (DateTimeImmutable $date) => $date->format('N') >= 6
        ? 'The deploy cannot run on a weekend.'
        : null,
    hint: 'The deploy will run at midnight UTC.',
);

var_dump($date);

echo str_repeat(PHP_EOL, 5);
