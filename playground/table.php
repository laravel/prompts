<?php

use function Laravel\Prompts\table;

require __DIR__.'/../vendor/autoload.php';

table(
    ['Name', 'Twitter'],
    [
        ['Taylor Otwell', '@taylorotwell'],
        ['Dries Vints', '@driesvints'],
        ['James Brooks', '@jbrooksuk'],
        ['Nuno Maduro', '@enunomaduro'],
        ['Mior Muhammad Zaki', '@crynobone'],
        ['Jess Archer', '@jessarchercodes'],
        ['Guus Leeuw', '@phpguus'],
        ['Tim MacDonald', '@timacdonald87'],
        ['Joe Dixon', '@_joedixon'],
    ],
);
