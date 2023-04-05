<?php

use function Laravel\Prompts\confirm;

require __DIR__.'/../vendor/autoload.php';

$confirmed = confirm(
    message: 'Would you like to install dependencies?',
);

var_dump($confirmed);

echo str_repeat(PHP_EOL, 2);
