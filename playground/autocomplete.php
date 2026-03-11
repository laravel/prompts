<?php

use function Laravel\Prompts\autocomplete;

require __DIR__ . '/../vendor/autoload.php';

$email = autocomplete(
    label: 'What is your email address',
    placeholder: 'E.g. taylor@laravel.com',
    validate: fn($value) => match (true) {
        strlen($value) === 0 => 'Please enter an email address.',
        ! filter_var($value, FILTER_VALIDATE_EMAIL) => 'Please enter a valid email address.',
        default => null,
    },
    hint: 'We will never share your email address with anyone else.',
    transform: fn($value) => strtolower($value),
    options: [
        'taylor@laravel.com',
        'dries@laravel.com',
        'james@laravel.com',
        'nuno@laravel.com',
        'mior@laravel.com',
        'jess@laravel.com',
    ],
);

var_dump($email);

echo str_repeat(PHP_EOL, 5);
