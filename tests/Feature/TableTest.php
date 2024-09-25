<?php

use Laravel\Prompts\Prompt;

use function Laravel\Prompts\table;

it('renders a table with headers', function ($headers, $rows) {
    Prompt::fake();

    table($headers, $rows);

    Prompt::assertStrippedOutputContains(<<<'OUTPUT'
         ┌────────────────────┬──────────────────┐
         │ Name               │ Twitter          │
         ├────────────────────┼──────────────────┤
         │ Taylor Otwell      │ @taylorotwell    │
         │ Dries Vints        │ @driesvints      │
         │ James Brooks       │ @jbrooksuk       │
         │ Nuno Maduro        │ @enunomaduro     │
         │ Mior Muhammad Zaki │ @crynobone       │
         │ Jess Archer        │ @jessarchercodes │
         │ Guus Leeuw         │ @phpguus         │
         │ Tim MacDonald      │ @timacdonald87   │
         │ Joe Dixon          │ @_joedixon       │
         └────────────────────┴──────────────────┘
        OUTPUT);
})->with([
    'arrays' => [
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
    ],
    ...depends_on_collection() ? [
        'collections' => [
            collect(['Name', 'Twitter']),
            collect([
                ['Taylor Otwell', '@taylorotwell'],
                ['Dries Vints', '@driesvints'],
                ['James Brooks', '@jbrooksuk'],
                ['Nuno Maduro', '@enunomaduro'],
                ['Mior Muhammad Zaki', '@crynobone'],
                ['Jess Archer', '@jessarchercodes'],
                ['Guus Leeuw', '@phpguus'],
                ['Tim MacDonald', '@timacdonald87'],
                ['Joe Dixon', '@_joedixon'],
            ]),
        ],
    ] : [],
]);

it('renders a table without headers', function ($rows) {
    Prompt::fake();

    table($rows);

    Prompt::assertStrippedOutputContains(<<<'OUTPUT'
         ┌────────────────────┬──────────────────┐
         │ Taylor Otwell      │ @taylorotwell    │
         │ Dries Vints        │ @driesvints      │
         │ James Brooks       │ @jbrooksuk       │
         │ Nuno Maduro        │ @enunomaduro     │
         │ Mior Muhammad Zaki │ @crynobone       │
         │ Jess Archer        │ @jessarchercodes │
         │ Guus Leeuw         │ @phpguus         │
         │ Tim MacDonald      │ @timacdonald87   │
         │ Joe Dixon          │ @_joedixon       │
         └────────────────────┴──────────────────┘
        OUTPUT);
})->with([
    'arrays' => [
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
    ],
    ...depends_on_collection() ? [
        'collections' => [
            collect([
                ['Taylor Otwell', '@taylorotwell'],
                ['Dries Vints', '@driesvints'],
                ['James Brooks', '@jbrooksuk'],
                ['Nuno Maduro', '@enunomaduro'],
                ['Mior Muhammad Zaki', '@crynobone'],
                ['Jess Archer', '@jessarchercodes'],
                ['Guus Leeuw', '@phpguus'],
                ['Tim MacDonald', '@timacdonald87'],
                ['Joe Dixon', '@_joedixon'],
            ]),
        ],
    ] : [],
]);
