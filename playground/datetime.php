<?php

use function Laravel\Prompts\datetime;

require __DIR__.'/../vendor/autoload.php';

$datetime = datetime(
    label: 'When should the maintenance window start?',
    default: 'tomorrow 22:00',
    min: 'today',
    weekStartsOn: 0,
);

var_dump($datetime);

echo str_repeat(PHP_EOL, 5);
