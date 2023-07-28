<?php

use Laravel\Prompts\P;

require __DIR__.'/../vendor/autoload.php';

$permissions = P::multiselect(
    label: 'What permissions should the user have?',
    options: [
        'view' => 'View',
        'create' => 'Create',
        'update' => 'Update',
        'delete' => 'Delete',
        'restore' => 'Restore',
        'force-delete' => 'Force delete',
    ],
    validate: fn ($values) => match (true) {
        empty($values) => 'Please select at least one permission.',
        default => null,
    },
);

var_dump($permissions);

echo str_repeat(PHP_EOL, 1);
