<?php

use function Laravel\Prompts\suggest;

require __DIR__.'/../vendor/autoload.php';

$model = suggest(
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
    hint: 'The model name should be singular.',
    description: 'Select the Eloquent model that the policy should be associated with. The policy will govern access control for this specific model type.

You can type to filter the available options or select from the dropdown list.',
);

var_dump($model);

echo str_repeat(PHP_EOL, 6);
