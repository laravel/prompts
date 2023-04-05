<?php

use function Laravel\Prompts\password;

require __DIR__.'/../vendor/autoload.php';

$password = password(
    message: 'Please provide a password',
    validate: fn ($value) => match (true) {
        ! $value => 'Please enter a password.',
        strlen($value) < 8 => 'Password should have at least 8 characters.',
        default => null,
    },
);

var_dump($password);

echo str_repeat(PHP_EOL, 5);
