<?php

use function Laravel\Prompts\alert;
use function Laravel\Prompts\steps;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\info;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;
use function Laravel\Prompts\warning;

require __DIR__ . '/../vendor/autoload.php';

$responses = steps(fn() => text('What should we call your project?'), title: 'Project Info')
    ->then(
        fn ($project) => select("Which database would you like to use for {$project}?", ['MySQL', 'PostGreSQL', 'SQLite']),
        revert: fn ($database) => info("Deleting database {$database}"),
    )
    ->then(fn() => confirm('Confirm project creation?'))
    ->run();

var_dump($responses);
