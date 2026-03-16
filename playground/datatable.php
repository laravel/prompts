<?php

use function Laravel\Prompts\datatable;
use function Laravel\Prompts\info;

require __DIR__ . '/../vendor/autoload.php';

$result = datatable(
    label: 'Select a team member',
    headers: ['Name', 'Twitter'],
    rows: [
        'longueng' => ['A very very long one that is too long for the table to be honest', '@longun'],
        'taylor' => ['Taylor Otwell', '@taylorotwell'],
        'dries' => ['Dries Vints', '@driesvints'],
        'james' => ['James Brooks', '@jbrooksuk'],
        'nuno' => ['Nuno Maduro', '@enunomaduro'],
        'mior' => ['Mior Muhammad Zaki', '@crynobone'],
        'jess' => ['Jess Archer', '@jessarchercodes'],
        'tim' => ['Tim MacDonald', '@timacdonald87'],
        'joe' => ['Joe Dixon', '@_joedixon'],
        'joet' => ['Joe Tannenbaum', '@joetannenbaum'],
        'wendell' => ['Wendell Adriel', '@wendelladriel'],
        'pushpak' => ['Pushpak Patel', '@pushpak1300'],
    ],
    hint: 'Press / to search, Enter to select',
);

info("You selected: {$result}");
