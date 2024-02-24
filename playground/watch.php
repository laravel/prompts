<?php

use function Laravel\Prompts\info;
use function Laravel\Prompts\note;
use function Laravel\Prompts\progress;
use function Laravel\Prompts\table;
use function Laravel\Prompts\text;
use function Laravel\Prompts\watch;

require __DIR__ . '/../vendor/autoload.php';

watch(
    function () {
        static $iteration = 0;
        static $items = [];

        if (count($items) === 5) {
            array_shift($items);
        }

        $items[] = [$iteration += 1, (new Datetime())->format(DateTime::RFC850)];

        if (count($items) === 5) {
            info(sprintf('Now the table just scrolls, %s and counting...', $iteration));
        } else {
            info('Filling up the table...');
        }

        progress('a nice progressbar', 5)->advance($iteration % 5);

        $ralph = text('Ralph', default: 'Ralph Wiggum: I\'m ignored!');

        note($ralph);

        table(
            [
                'Iteration',
                'DateTime'
            ],
            $items
        );
    },
    1,
);
