<?php

use function Laravel\Prompts\pause;

require __DIR__.'/../vendor/autoload.php';

$continued = pause();

var_dump($continued);
