<?php

use function Laravel\Prompts\datatable;
use function Laravel\Prompts\info;

require __DIR__.'/../vendor/autoload.php';

$result = datatable(
    label: 'Select a team member',
    headers: ['Name', 'Twitter', 'Role'],
    rows: [
        'longueng' => ['A very very long one that is too long for the table to be honest and also this should never fit because it is actually wild but here we are', '@longun', 'Developer'],
        'taylor' => ['Taylor Otwell', '@taylorotwell', 'CEO'.PHP_EOL.'Developer'],
        'dries' => ['Dries Vints', '@driesvints', 'Developer'],
        'james' => ['James Brooks', '@jbrooksuk', 'Developer'],
        'nuno' => ['Nuno Maduro', '@enunomaduro', 'Developer'],
        'mior' => ['Mior Muhammad Zaki', '@crynobone', 'Developer'],
        'jess' => ['Jess Archer', '@jessarchercodes', 'Developer'],
        'tim' => ['Tim MacDonald', '@timacdonald87', 'Developer'],
        'joe' => ['Joe Dixon', '@_joedixon', 'Developer'],
        'joet' => ['Joe Tannenbaum', '@joetannenbaum', 'Developer'],
        'wendell' => ['Wendell Adriel', '@wendelladriel', 'Developer'],
        'pushpak' => ['Pushpak Patel', '@pushpak1300', 'Developer'],
    ],
);

info("You selected: {$result}");
