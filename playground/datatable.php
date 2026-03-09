<?php

use function Laravel\Prompts\datatable;

require __DIR__ . '/../vendor/autoload.php';

datatable(
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
    [
        'view' => fn($row) => "View {$row[0]}",
        'edit' => fn($row) => "Edit {$row[0]}",
        'delete' => fn($row) => "Delete {$row[0]}",
    ],
);
