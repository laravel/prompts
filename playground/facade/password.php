<?php

use Laravel\Prompts\Support\Facades\Prompt;

require __DIR__ . '/../../vendor/autoload.php';

$password = Prompt::password(
    label: 'Please provide a password',
    placeholder: 'Min 8 characters',
    required: true,
    validate: fn($value) => match (true) {
        strlen($value) < 8 => 'Password should have at least 8 characters.',
        default => null,
    },
    hint: 'Your password will be encrypted and stored securely.',
);

var_dump($password);

echo str_repeat(PHP_EOL, 5);
