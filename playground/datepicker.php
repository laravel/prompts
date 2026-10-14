<?php

use function Laravel\Prompts\datepicker;

require __DIR__.'/../vendor/autoload.php';

$date = datepicker(
    label: 'When should the deploy run?',
    default: '+3 days',
    min: 'today',
    max: '+1 year',
    validate: fn (DateTimeImmutable $date) => $date->format('N') >= 6
        ? 'The deploy cannot run on a weekend.'
        : null,
    hint: 'The deploy will run at midnight UTC.',
    calendar: in_array('--calendar', $argv),
);

var_dump($date);

echo str_repeat(PHP_EOL, 5);
