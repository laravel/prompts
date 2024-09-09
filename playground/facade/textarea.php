<?php

use Laravel\Prompts\Support\Facades\Prompt;

require __DIR__ . '/../../vendor/autoload.php';

$story = Prompt::textarea(
    label: 'Tell me a story',
    placeholder: 'Weave me a tale',
);

var_dump($story);

echo str_repeat(PHP_EOL, 5);
