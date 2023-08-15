<?php

use function Laravel\Prompts\password;

require __DIR__.'/../vendor/autoload.php';

$password = password(
    label: 'Please provide a password',
    placeholder: 'Min 8 characters',
    required: true,
    validate: fn ($value) => match (true) {
        strlen($value) < 8 => 'Password should have at least 8 characters.',
        default => null,
    },
    hint: 'Your password will be encrypted and stored securely.',
);

var_dump($password);

echo str_repeat(PHP_EOL, 5);
