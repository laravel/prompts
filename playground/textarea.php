<?php

use function Laravel\Prompts\textarea;

require __DIR__.'/../vendor/autoload.php';

$email = textarea(
    label: 'Tell me a story',
    default: collect([
        'first line',
        'second line',
        'third line though',
        'fourth line wow',
        'fifth line are you kidding me',
        'sixth line here we go',
        'seventh line ok sure',
    ])->join(PHP_EOL),
);

var_dump($email);

echo str_repeat(PHP_EOL, 5);
