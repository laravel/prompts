<?php

use Laravel\Prompts\Prompt;

use function Laravel\Prompts\table;

it('renders a table', function ($headers, $rows) {
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
]);
