<?php

use function Laravel\Prompts\textarea;

require __DIR__.'/../vendor/autoload.php';

$story = textarea(
    label: 'Tell me a story',
    placeholder: 'Weave me a tale',
);

var_dump($story);

echo str_repeat(PHP_EOL, 5);
