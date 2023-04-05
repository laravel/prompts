<?php

use function Laravel\Prompts\spin;

require __DIR__.'/../vendor/autoload.php';

$result = spin(
    function () {
        sleep(4);

        return 'Callback return';
    },
    'Installing dependencies...',
);

echo PHP_EOL;

var_dump($result);

echo str_repeat(PHP_EOL, 6);
