<?php

use function Laravel\Prompts\title;

require __DIR__.'/../vendor/autoload.php';

title('Hello Prompts!');

sleep(2);

title('Still there?');

sleep(2);

title('');
