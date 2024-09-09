<?php

use Laravel\Prompts\Support\Facades\Prompt;

require __DIR__ . '/../../vendor/autoload.php';

Prompt::table(
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
