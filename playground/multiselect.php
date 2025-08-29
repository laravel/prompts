<?php

use function Laravel\Prompts\multiselect;

require __DIR__.'/../vendor/autoload.php';

$permissions = multiselect(
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
    hint: 'The permissions will determine what the user can do.',
    description: 'Select the permissions you want to grant to this user. You can choose multiple permissions by using the space bar to toggle each option.

Be careful when granting delete and force-delete permissions as these cannot be undone.'
);

var_dump($permissions);

echo str_repeat(PHP_EOL, 1);
