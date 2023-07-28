<?php

use Laravel\Prompts\P;

require __DIR__.'/../vendor/autoload.php';

$result = P::spin(
    function () {
        sleep(4);

        return 'Callback return';
    },
    'Installing dependencies...',
);

echo PHP_EOL;

var_dump($result);

echo str_repeat(PHP_EOL, 6);
