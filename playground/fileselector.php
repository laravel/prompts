<?php

use function Laravel\Prompts\fileselector;

require __DIR__.'/../vendor/autoload.php';

$model = fileselector(
    label: 'What model should the policy apply to?',
    placeholder: 'E.g. User',
    validate: fn ($value) => match (true) {
        strlen($value) === 0 => 'Please enter a model name.',
        default => null,
    },
    hint: 'The model name should be singular.',
);

var_dump($model);

echo str_repeat(PHP_EOL, 6);
