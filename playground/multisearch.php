<?php

use function Laravel\Prompts\multisearch;

require __DIR__.'/../vendor/autoload.php';

$users = collect([
    'taylor' => 'Taylor Otwell',
    'dries' => 'Dries Vints',
    'james' => 'James Brooks',
    'nuno' => 'Nuno Maduro',
    'mior' => 'Mior Muhammad Zaki',
    'jess' => 'Jess Archer',
    'guus' => 'Guus Leeuw',
    'tim' => 'Tim MacDonald',
    'joe' => 'Joe Dixon',
]);

$selected = multisearch(
    label: 'Which users should receive the email?',
    placeholder: 'Search...',
    options: function ($value) use ($users) {
        // Comment to show all results by default.
        if (strlen($value) === 0) {
            return [];
        }

        usleep(100 * 1000); // Simulate a DB query.

        return $users->when(
            strlen($value),
            fn ($users) => $users->filter(fn ($name) => str_contains(strtolower($name), strtolower($value)))
        )->all();
    },
    required: true,
    validate: function ($values) {
        if (in_array('jess', $values)) {
            return 'Jess cannot receive emails';
        }
    },
);

var_dump($selected);
