<?php

use Laravel\Prompts\Support\Logger;

use function Laravel\Prompts\info;
use function Laravel\Prompts\select;
use function Laravel\Prompts\task;

require __DIR__.'/../vendor/autoload.php';

$ansiOutput = [
    "\e[32mInstalling dependencies from lock file (including require-dev)\e[39m",
    "\e[32mVerifying lock file contents can be installed on current platform.\e[39m",
    "\e[32mPackage operations: 31 installs, 0 updates, 0 removals\e[39m",
    "    0 [>---------------------------]\e[1G\e[2K    0 [->--------------------------]\e[1G\e[2K    0 [--->------------------------]\e[1G\e[2K  - Installing \e[32milluminate/conditionable\e[39m (\e[33mv12.49.0\e[39m): Extracting archive",
    "  - Installing \e[32milluminate/macroable\e[39m (\e[33mv12.49.0\e[39m): Extracting archive",
    "  - Installing \e[32msymfony/polyfill-mbstring\e[39m (\e[33mv1.33.0\e[39m): Extracting archive",
    "  - Installing \e[32msymfony/polyfill-intl-normalizer\e[39m (\e[33mv1.33.0\e[39m): Extracting archive",
    "  - Installing \e[32msymfony/polyfill-intl-grapheme\e[39m (\e[33mv1.33.0\e[39m): Extracting archive",
    "  - Installing \e[32msymfony/polyfill-ctype\e[39m (\e[33mv1.33.0\e[39m): Extracting archive",
    "  - Installing \e[32msymfony/string\e[39m (\e[33mv8.0.4\e[39m): Extracting archive",
    "  - Installing \e[32msymfony/deprecation-contracts\e[39m (\e[33mv3.6.0\e[39m): Extracting archive",
    "  - Installing \e[32mpsr/container\e[39m (\e[33m2.0.2\e[39m): Extracting archive",
    "  - Installing \e[32msymfony/service-contracts\e[39m (\e[33mv3.6.1\e[39m): Extracting archive",
    "  - Installing \e[32msymfony/console\e[39m (\e[33mv7.4.4\e[39m): Extracting archive",
    "  - Installing \e[32mlaravel/prompts\e[39m (\e[33mv0.3.11\e[39m): Extracting archive",
    "  - Installing \e[32mnikic/php-parser\e[39m (\e[33mv5.7.0\e[39m): Extracting archive",
    "  - Installing \e[32mwebmozart/assert\e[39m (\e[33m1.12.1\e[39m): Extracting archive",
    "  - Installing \e[32mphpstan/phpdoc-parser\e[39m (\e[33m2.3.2\e[39m): Extracting archive",
    "  - Installing \e[32mphpdocumentor/reflection-common\e[39m (\e[33m2.2.0\e[39m): Extracting archive",
    "  - Installing \e[32mdoctrine/deprecations\e[39m (\e[33m1.1.5\e[39m): Extracting archive",
    "  - Installing \e[32mphpdocumentor/type-resolver\e[39m (\e[33m1.12.0\e[39m): Extracting archive",
    "  - Installing \e[32mphpdocumentor/reflection-docblock\e[39m (\e[33m5.6.6\e[39m): Extracting archive",
    "  - Installing \e[32mpsr/simple-cache\e[39m (\e[33m3.0.0\e[39m): Extracting archive",
    "  - Installing \e[32msymfony/finder\e[39m (\e[33mv8.0.5\e[39m): Extracting archive",
    "  - Installing \e[32milluminate/contracts\e[39m (\e[33mv12.49.0\e[39m): Extracting archive",
    "  - Installing \e[32mspatie/laravel-package-tools\e[39m (\e[33m1.92.7\e[39m): Extracting archive",
    "  - Installing \e[32msymfony/polyfill-php85\e[39m (\e[33mv1.33.0\e[39m): Extracting archive",
    "  - Installing \e[32msymfony/polyfill-php84\e[39m (\e[33mv1.33.0\e[39m): Extracting archive",
    "  - Installing \e[32msymfony/polyfill-php83\e[39m (\e[33mv1.33.0\e[39m): Extracting archive",
    "  - Installing \e[32milluminate/collections\e[39m (\e[33mv12.49.0\e[39m): Extracting archive",
    "  - Installing \e[32mspatie/php-structure-discoverer\e[39m (\e[33m2.3.3\e[39m): Extracting archive",
    "  - Installing \e[32msymfony/polyfill-php80\e[39m (\e[33mv1.33.0\e[39m): Extracting archive",
    "  - Installing \e[32mphpdocumentor/reflection\e[39m (\e[33m6.4.4\e[39m): Extracting archive",
    "  - Installing \e[32mspatie/laravel-data\e[39m (\e[33m4.19.1\e[39m): Extracting archive",
    "  0/31 [>---------------------------]   0%\e[1G\e[2K 23/31 [====================>-------]  74%\e[1G\e[2K 31/31 [============================] 100%\e[1G\e[2K\e[32mGenerating autoload files\e[39m",
    "\e[32m16 packages you are using are looking for funding.\e[39m",
    "\e[32mUse the `composer fund` command to find out more!\e[39m",
];

$plainOutput = [
    'Installing dependencies from lock file (including require-dev)',
    'Verifying lock file contents can be installed on current platform.',
    'Package operations: 31 installs, 0 updates, 0 removals',
    '  - Installing illuminate/conditionable (v12.49.0): Extracting archive',
    '  - Installing illuminate/macroable (v12.49.0): Extracting archive',
    '  - Installing symfony/polyfill-mbstring (v1.33.0): Extracting archive',
    '  - Installing symfony/polyfill-intl-normalizer (v1.33.0): Extracting archive',
    '  - Installing symfony/polyfill-intl-grapheme (v1.33.0): Extracting archive',
    '  - Installing symfony/polyfill-ctype (v1.33.0): Extracting archive',
    '  - Installing symfony/string (v8.0.4): Extracting archive',
    '  - Installing symfony/deprecation-contracts (v3.6.0): Extracting archive',
    '  - Installing psr/container (2.0.2): Extracting archive',
    '  - Installing symfony/service-contracts (v3.6.1): Extracting archive',
    '  - Installing symfony/console (v7.4.4): Extracting archive',
    '  - Installing laravel/prompts (v0.3.11): Extracting archive',
    '  - Installing nikic/php-parser (v5.7.0): Extracting archive',
    '  - Installing webmozart/assert (1.12.1): Extracting archive',
    '  - Installing phpstan/phpdoc-parser (2.3.2): Extracting archive',
    '  - Installing phpdocumentor/reflection-common (2.2.0): Extracting archive',
    '  - Installing doctrine/deprecations (1.1.5): Extracting archive',
    '  - Installing phpdocumentor/type-resolver (1.12.0): Extracting archive',
    '  - Installing phpdocumentor/reflection-docblock (5.6.6): Extracting archive',
    '  - Installing psr/simple-cache (3.0.0): Extracting archive',
    '  - Installing symfony/finder (v8.0.5): Extracting archive',
    '  - Installing illuminate/contracts (v12.49.0): Extracting archive',
    '  - Installing spatie/laravel-package-tools (1.92.7): Extracting archive',
    '  - Installing symfony/polyfill-php85 (v1.33.0): Extracting archive',
    '  - Installing symfony/polyfill-php84 (v1.33.0): Extracting archive',
    '  - Installing symfony/polyfill-php83 (v1.33.0): Extracting archive',
    '  - Installing illuminate/collections (v12.49.0): Extracting archive',
    '  - Installing spatie/php-structure-discoverer (2.3.3): Extracting archive',
    '  - Installing symfony/polyfill-php80 (v1.33.0): Extracting archive',
    '  - Installing phpdocumentor/reflection (6.4.4): Extracting archive',
    '  - Installing spatie/laravel-data (4.19.1): Extracting archive',
    'Generating autoload files',
    '16 packages you are using are looking for funding.',
    'Use the `composer fund` command to find out more!',
];

$commands = [
    [
        $plainOutput,
        'Installing dependencies...',
        'Dependencies installed!',
        'success',
    ],
    [
        $ansiOutput,
        'Installing dependencies with ANSI...',
        'Dependencies with ANSI not installed!',
        'error',
    ],
    [
        $plainOutput,
        'Re-installing dependencies...',
        'Dependencies perhaps not installed!',
        'warning',
    ],
    [
        $ansiOutput,
        'Installing dependencies with ANSI... (again)',
        'Dependencies with ANSI installed!',
        'success',
    ],
];

$streamWords = explode(' ', 'The quick brown fox jumps over the lazy dog and then keeps on running through the meadow until it reaches the edge of the forest where it pauses to catch its breath before continuing on its journey through the tall pine trees. Meanwhile the dog finally wakes up and realizes what happened so it stretches its legs and begins to chase after the fox following the trail of footprints left behind in the soft morning dew across the countryside.');

$mode = select('Which demo?', [
    'standard' => 'Standard (line-by-line with stable messages)',
    'stream' => 'Stream (streaming text accumulation)',
    'mixed' => 'Mixed (lines, then stream, then lines)',
]);

match ($mode) {
    'standard' => task(
        label: $commands[0][1],
        callback: function (Logger $logger) use ($commands) {
            foreach ($commands as $data) {
                [$output, $label, $message, $type] = $data;
                $logger->label($label);

                foreach ($output as $line) {
                    $logger->line($line);
                    usleep(100_000);
                }

                $logger->{$type}($message);
            }
        },
    ),

    'stream' => task(
        label: 'Streaming text...',
        callback: function (Logger $logger) use ($streamWords) {
            $logger->line('START');

            foreach ($streamWords as $word) {
                $logger->partial($word.' ');
                usleep(100_000);
            }

            $logger->commitPartial();
            $logger->line('END');

            usleep(300_000);
        },
    ),

    'mixed' => task(
        label: 'Running mixed demo...',
        callback: function (Logger $logger) use ($plainOutput, $streamWords) {
            $logger->label('Installing dependencies...');

            foreach (array_slice($plainOutput, 0, 10) as $line) {
                $logger->line($line);
                usleep(100_000);
            }

            $logger->success('Dependencies installed!');

            $logger->label('Generating output...');

            foreach ($streamWords as $word) {
                $logger->partial($word.' ');
                usleep(100_000);
            }

            $logger->commitPartial();

            $logger->label('Cleaning up...');

            foreach (array_slice($plainOutput, 0, 5) as $line) {
                $logger->line($line);
                usleep(100_000);
            }

            $logger->success('All done!');
        },
    ),
};

echo PHP_EOL;

info('We did it! So many dependencies.');

echo str_repeat(PHP_EOL, 5);
