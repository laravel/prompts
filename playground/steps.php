<?php

use Laravel\Prompts\StepPrompt;
use Laravel\Prompts\Step;
use function Laravel\Prompts\alert;
use function Laravel\Prompts\steps;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\info;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;
use function Laravel\Prompts\warning;

require __DIR__ . '/../vendor/autoload.php';

$responses = steps(title: 'Project Information')
    ->add(fn() => text('What is the name of your project?'), revert: false)
    ->add(fn($responses) => select(
        "Which database would you like to use for {$responses[0]}?", ['MySQL', 'MariaDB', 'SQLite', 'PostGreSQL']
    ), revert: fn() => alert('Deleting created database…'))
    ->add(fn ($responses) => confirm("Confirm creation of {$responses[0]} using {$responses[1]} as a database?"))
    ->display();

var_dump($responses);
