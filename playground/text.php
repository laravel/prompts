<?php

use function Laravel\Prompts\text;

require __DIR__.'/../vendor/autoload.php';

$email = text(
    label: 'What is your email address',
    placeholder: 'E.g. taylor@laravel.com',
    validate: fn ($value) => match (true) {
        strlen($value) === 0 => 'Please enter an email address.',
        ! filter_var($value, FILTER_VALIDATE_EMAIL) => 'Please enter a valid email address.',
        default => null,
    },
    hint: 'We will never share your email address with anyone else.',
    transform: fn ($value) => strtolower($value),
    description: 'Please provide your email address to create your account. This will be used for account verification, important notifications, and password recovery.

Your email address will be kept secure and will only be used for account-related communications.',
);

var_dump($email);

echo str_repeat(PHP_EOL, 5);
