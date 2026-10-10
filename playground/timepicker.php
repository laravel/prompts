<?php

use function Laravel\Prompts\timepicker;

require __DIR__.'/../vendor/autoload.php';

$time = timepicker(
    label: 'When should the maintenance window start?',
    default: '22:00',
    withSeconds: in_array('--seconds', $argv),
    use12Hours: in_array('--12h', $argv),
);

var_dump($time);

echo str_repeat(PHP_EOL, 5);
