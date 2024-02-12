<?php

use function Laravel\Prompts\alert;
use function Laravel\Prompts\steps;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\select;
use function Laravel\Prompts\suggest;
use function Laravel\Prompts\text;

require __DIR__ . '/../vendor/autoload.php';

$responses = steps(title: 'Project Information')
    ->add(fn() => text('What is the name of your project?'))
    ->add(fn() => suggest('Which OS would you like to deploy on?', ['Windows', 'Mac', 'Linux']), revert: false)
    ->add(fn($responses) => select(
        "Which database would you like to use for {$responses[0]}?", ['MySQL', 'MariaDB', 'SQLite', 'PostGreSQL']
    ), revert: fn() => alert('Deleting created database…'))
    ->add(fn ($responses) => confirm("Confirm creation of {$responses[0]} on {$responses[1]} using {$responses[2]} as a database?"))
    ->display();

var_dump($responses);
