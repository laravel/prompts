<?php

use function Laravel\Prompts\datetimepicker;

require __DIR__.'/../vendor/autoload.php';

$datetime = datetimepicker(
    label: 'When should the maintenance window start?',
    default: 'tomorrow 22:00',
    min: 'today',
    weekStartsOn: 0,
    withSeconds: in_array('--seconds', $argv),
    calendar: in_array('--calendar', $argv),
    use12Hours: in_array('--12h', $argv),
);

var_dump($datetime);

echo str_repeat(PHP_EOL, 5);
