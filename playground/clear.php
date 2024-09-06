<?php

use function Laravel\Prompts\clear;
use function Laravel\Prompts\info;
use function Laravel\Prompts\note;
use function Laravel\Prompts\outro;
use function Laravel\Prompts\pause;
use function Laravel\Prompts\table;

require __DIR__ . '/../vendor/autoload.php';

note('Writing some text that will disapear.');
pause('When you type [enter] the terminal will be clean.');

clear();

table(
    ['name', 'email'],
    [
        ['Random Name', 'mail@mail.com'],
        ['Another Random Name', 'another.mail@mail.com'],
        ['Some wird Name huh?', 'wierd@mail.com'],
    ]
);

pause('Press [enter] again to clear the terminal.');

clear();

info('This is your secret key, copy it and keep it in somewhere safe.');
note(md5(random_int(10, 1000)));

pause('Press [enter] to continue');

clear();

outro('Bye! :)');
