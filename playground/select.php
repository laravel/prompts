<?php

use function Laravel\Prompts\select;

require __DIR__.'/../vendor/autoload.php';

$role = select(
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
    },
    hint: 'The role will determine what the user can do.',
);

var_dump($role);

echo str_repeat(PHP_EOL, 5);
