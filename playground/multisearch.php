<?php

use function Laravel\Prompts\multisearch;

require __DIR__.'/../vendor/autoload.php';

$models = multisearch(
    label: 'Which users should receive the email?',
    placeholder: 'Search...',
    returnKeys: true,
    options: function ($value) {
        if (strlen($value) === 0) {
            return [];
        }

        usleep(100 * 1000);

        $min = min(strlen($value)-1, 10);
        $max = max(9, 20 - strlen($value));

        if ($max - $min < 0) {
            return [];
        }

        $data = [];

        foreach (range($min, $max) as $id) {
            $data["user-$id"] = "User $id";
        }

        return $data;
    },
    validate: function ($values) {
        if (in_array('id-1', $values)) {
            return 'User 1 cannot receive emails';
        }
    },
    required: true,
);

var_dump($models);

// echo str_repeat(PHP_EOL, 5);
