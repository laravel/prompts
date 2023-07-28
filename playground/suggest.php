<?php

use Laravel\Prompts\P;

require __DIR__.'/../vendor/autoload.php';

$model = P::suggest(
    label: 'What model should the policy apply to?',
    placeholder: 'E.g. User',
    options: [
        'Article',
        'Destination',
        'Flight',
        'Membership',
        'Role',
        'Team',
        'TeamInvitation',
        'User',
    ],
    validate: fn ($value) => match (true) {
        strlen($value) === 0 => 'Please enter a model name.',
        default => null,
    },
);

var_dump($model);

echo str_repeat(PHP_EOL, 6);
