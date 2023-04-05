<?php

use function Laravel\Prompts\select;

require __DIR__.'/../vendor/autoload.php';

$role = select(
    message: 'What role should the user have?',
    options: [
        'read-only' => 'Read only',
        'member' => 'Member',
        'contributor' => 'Contributor',
        'supervisor' => 'Supervisor',
        'manager' => 'Manager',
        'admin' => 'Administrator',
        'owner' => 'Owner',
    ],
);

var_dump($role);

echo str_repeat(PHP_EOL, 5);
