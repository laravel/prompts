<?php

use function Laravel\Prompts\clear;
use function Laravel\Prompts\note;
use function Laravel\Prompts\pause;

require __DIR__.'/../vendor/autoload.php';

note('This will disappear.');

pause('Press [Enter] to continue.');

clear();

note('This will also disappear.');

pause('Press [Enter] to continue.');

clear();
