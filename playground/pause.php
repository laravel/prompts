<?php

use Laravel\Prompts\Prompt;
use function Laravel\Prompts\pause;

require __DIR__.'/../vendor/autoload.php';

Prompt::fallbackWhen(true);


$continued = pause();

var_dump($continued);

echo str_repeat(PHP_EOL, 2);
