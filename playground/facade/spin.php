<?php

use Laravel\Prompts\Support\Facades\Prompt;

require __DIR__ . '/../../vendor/autoload.php';

$result = Prompt::spin(
    function () {
        sleep(4);

        return 'Callback return';
    },
    'Installing dependencies...',
);

echo PHP_EOL;

var_dump($result);

echo str_repeat(PHP_EOL, 6);
