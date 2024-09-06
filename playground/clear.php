<?php

use function Laravel\Prompts\clear;
use function Laravel\Prompts\note;
use function Laravel\Prompts\pause;

require __DIR__ . '/../vendor/autoload.php';

note('Writing some text that will disapear.');
pause('When you type [enter] the terminal will be clean.');

clear();

note('All clear!');
