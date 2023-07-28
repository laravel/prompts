<?php

use Laravel\Prompts\P;

require __DIR__.'/../vendor/autoload.php';

$role = P::select(
    label: 'What role should the user have?',
    options: [
        'read-only' => 'Read only',
        'member' => 'Member',
        'contributor' => 'Contributor',
        'supervisor' => 'Supervisor',
        'manager' => 'Manager',
        'admin' => 'Administrator',
        'owner' => 'Owner',
    ],
    validate: fn ($value) => match ($value) {
        'owner' => 'The owner role is already assigned.',
        default => null
    }
);

var_dump($role);

echo str_repeat(PHP_EOL, 5);
