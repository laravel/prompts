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

spin(
    function () {
        sleep(2);
    },
    'Starting process...',
)->then(
    function () {
        sleep(2);
    },
    'Installing dependencies...',
)->then(
    function () {
        sleep(2);
    },
    'Preparing environment...',
)->then(
    function () {
        sleep(2);
    },
    'Finishing up...',
)->then(
    function () {
        sleep(1);
    },
    'Done!',
);
