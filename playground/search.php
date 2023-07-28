<?php

use Laravel\Prompts\P;

require __DIR__.'/../vendor/autoload.php';

$model = P::search(
    label: 'Which user should receive the email?',
    placeholder: 'Search...',
    options: function ($value) {
        if (strlen($value) === 0) {
            return [];
        }

        usleep(100 * 1000);

        $count = max(0, 10 - strlen($value));

        if ($count === 0) {
            return [];
        }

        return array_map(
            fn ($id) => "User $id",
            range(0, $count)
        );
    },
    validate: function ($value) {
        if ($value === '0') {
            return 'User 0 is not allowed to receive emails.';
        }
    }
);

var_dump($model);

echo str_repeat(PHP_EOL, 6);
