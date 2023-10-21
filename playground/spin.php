<?php

use function Laravel\Prompts\spin;

require __DIR__ . '/../vendor/autoload.php';

$result1 = spin(
    function () {
        sleep(4);

        return 'Callback return';
    },
    'Installing dependencies...',
);

$result2 = spin(
    function () {
        sleep(4);

        return 'A-OK';
    },
    'Checking system...',
    'System looks good!',
);

$result3 = spin(
    function () {
        sleep(4);

        return '8.2';
    },
    'Detecting PHP Version...',
    fn ($result) => "PHP Version: <info>{$result}</info>",
);

echo PHP_EOL;

var_dump($result1, $result2, $result3);

echo str_repeat(PHP_EOL, 6);
