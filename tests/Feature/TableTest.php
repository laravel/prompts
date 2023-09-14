<?php

use Laravel\Prompts\Prompt;

use function Laravel\Prompts\table;

it('renders a table', function ($headers, $rows) {
    Prompt::fake();

    table($headers, $rows);

    Prompt::assertStrippedOutputContains('┌────────────────────┬───────────────────┐');
    Prompt::assertStrippedOutputContains('│ Name               │ Twitter           │');
    Prompt::assertStrippedOutputContains('├────────────────────┼───────────────────┤');
    Prompt::assertStrippedOutputContains('│ Taylor Otwell      │ @taylorotwell     │');
    Prompt::assertStrippedOutputContains('│ Dries Vints        │ @driesvints       │');
    Prompt::assertStrippedOutputContains('│ James Brooks       │ @jbrooksuk        │');
    Prompt::assertStrippedOutputContains('│ Nuno Maduro        │ @enunomaduro      │');
    Prompt::assertStrippedOutputContains('│ Mior Muhammad Zaki │ @crynobone        │');
    Prompt::assertStrippedOutputContains('│ Jess Archer        │ @jessarchercodes  │');
    Prompt::assertStrippedOutputContains('│ Guus Leeuw         │ @phpguus          │');
    Prompt::assertStrippedOutputContains('│ Tim MacDonald      │ @timacdonald87    │');
    Prompt::assertStrippedOutputContains('│ Joe Dixon          │ @_joedixon        │');
    Prompt::assertStrippedOutputContains('└────────────────────┴───────────────────┘');
})->with([
    [
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
    [
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
