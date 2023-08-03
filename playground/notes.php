<?php

use function Laravel\Prompts\alert;
use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\intro;
use function Laravel\Prompts\note;
use function Laravel\Prompts\outro;
use function Laravel\Prompts\warning;

require __DIR__.'/../vendor/autoload.php';

intro('Intro');
note('Note');
info('Info');
warning('Warning');
error('Error');
alert('Alert');
outro('Outro');
