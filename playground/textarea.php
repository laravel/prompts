<?php

use function Laravel\Prompts\textarea;

require __DIR__.'/../vendor/autoload.php';

$email = textarea(
    label: 'Tell me a story',
    placeholder: 'Weave me a tale',
);

var_dump($email);

echo str_repeat(PHP_EOL, 5);
