<?php

use Laravel\Prompts\P;

require __DIR__.'/../vendor/autoload.php';

$password = P::password(
    label: 'Please provide a password',
    placeholder: 'Min 8 characters',
    required: true,
    validate: fn ($value) => match (true) {
        strlen($value) < 8 => 'Password should have at least 8 characters.',
        default => null,
    },
);

var_dump($password);

echo str_repeat(PHP_EOL, 5);
