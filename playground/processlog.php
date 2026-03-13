<?php

use Laravel\Prompts\Support\Logger;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\info;
use function Laravel\Prompts\processLog;

require __DIR__ . '/../vendor/autoload.php';

$ansiOutput = [
    "[32mInstalling dependencies from lock file (including require-dev)[39m",
    "[32mVerifying lock file contents can be installed on current platform.[39m",
    "[32mPackage operations: 31 installs, 0 updates, 0 removals[39m",
    "    0 [>---------------------------][1G[2K    0 [->--------------------------][1G[2K    0 [--->------------------------][1G[2K  - Installing [32milluminate/conditionable[39m ([33mv12.49.0[39m): Extracting archive",
    "  - Installing [32milluminate/macroable[39m ([33mv12.49.0[39m): Extracting archive",
    "  - Installing [32msymfony/polyfill-mbstring[39m ([33mv1.33.0[39m): Extracting archive",
    "  - Installing [32msymfony/polyfill-intl-normalizer[39m ([33mv1.33.0[39m): Extracting archive",
    "  - Installing [32msymfony/polyfill-intl-grapheme[39m ([33mv1.33.0[39m): Extracting archive",
    "  - Installing [32msymfony/polyfill-ctype[39m ([33mv1.33.0[39m): Extracting archive",
    "  - Installing [32msymfony/string[39m ([33mv8.0.4[39m): Extracting archive",
    "  - Installing [32msymfony/deprecation-contracts[39m ([33mv3.6.0[39m): Extracting archive",
    "  - Installing [32mpsr/container[39m ([33m2.0.2[39m): Extracting archive",
    "  - Installing [32msymfony/service-contracts[39m ([33mv3.6.1[39m): Extracting archive",
    "  - Installing [32msymfony/console[39m ([33mv7.4.4[39m): Extracting archive",
    "  - Installing [32mlaravel/prompts[39m ([33mv0.3.11[39m): Extracting archive",
    "  - Installing [32mnikic/php-parser[39m ([33mv5.7.0[39m): Extracting archive",
    "  - Installing [32mwebmozart/assert[39m ([33m1.12.1[39m): Extracting archive",
    "  - Installing [32mphpstan/phpdoc-parser[39m ([33m2.3.2[39m): Extracting archive",
    "  - Installing [32mphpdocumentor/reflection-common[39m ([33m2.2.0[39m): Extracting archive",
    "  - Installing [32mdoctrine/deprecations[39m ([33m1.1.5[39m): Extracting archive",
    "  - Installing [32mphpdocumentor/type-resolver[39m ([33m1.12.0[39m): Extracting archive",
    "  - Installing [32mphpdocumentor/reflection-docblock[39m ([33m5.6.6[39m): Extracting archive",
    "  - Installing [32mpsr/simple-cache[39m ([33m3.0.0[39m): Extracting archive",
    "  - Installing [32msymfony/finder[39m ([33mv8.0.5[39m): Extracting archive",
    "  - Installing [32milluminate/contracts[39m ([33mv12.49.0[39m): Extracting archive",
    "  - Installing [32mspatie/laravel-package-tools[39m ([33m1.92.7[39m): Extracting archive",
    "  - Installing [32msymfony/polyfill-php85[39m ([33mv1.33.0[39m): Extracting archive",
    "  - Installing [32msymfony/polyfill-php84[39m ([33mv1.33.0[39m): Extracting archive",
    "  - Installing [32msymfony/polyfill-php83[39m ([33mv1.33.0[39m): Extracting archive",
    "  - Installing [32milluminate/collections[39m ([33mv12.49.0[39m): Extracting archive",
    "  - Installing [32mspatie/php-structure-discoverer[39m ([33m2.3.3[39m): Extracting archive",
    "  - Installing [32msymfony/polyfill-php80[39m ([33mv1.33.0[39m): Extracting archive",
    "  - Installing [32mphpdocumentor/reflection[39m ([33m6.4.4[39m): Extracting archive",
    "  - Installing [32mspatie/laravel-data[39m ([33m4.19.1[39m): Extracting archive",
    "  0/31 [>---------------------------]   0%[1G[2K 23/31 [====================>-------]  74%[1G[2K 31/31 [============================] 100%[1G[2K[32mGenerating autoload files[39m",
    "[32m16 packages you are using are looking for funding.[39m",
    "[32mUse the `composer fund` command to find out more![39m",
];

$plainOutput = [
    "Installing dependencies from lock file (including require-dev)",
    "Verifying lock file contents can be installed on current platform.",
    "Package operations: 31 installs, 0 updates, 0 removals",
    "  - Installing illuminate/conditionable (v12.49.0): Extracting archive",
    "  - Installing illuminate/macroable (v12.49.0): Extracting archive",
    "  - Installing symfony/polyfill-mbstring (v1.33.0): Extracting archive",
    "  - Installing symfony/polyfill-intl-normalizer (v1.33.0): Extracting archive",
    "  - Installing symfony/polyfill-intl-grapheme (v1.33.0): Extracting archive",
    "  - Installing symfony/polyfill-ctype (v1.33.0): Extracting archive",
    "  - Installing symfony/string (v8.0.4): Extracting archive",
    "  - Installing symfony/deprecation-contracts (v3.6.0): Extracting archive",
    "  - Installing psr/container (2.0.2): Extracting archive",
    "  - Installing symfony/service-contracts (v3.6.1): Extracting archive",
    "  - Installing symfony/console (v7.4.4): Extracting archive",
    "  - Installing laravel/prompts (v0.3.11): Extracting archive",
    "  - Installing nikic/php-parser (v5.7.0): Extracting archive",
    "  - Installing webmozart/assert (1.12.1): Extracting archive",
    "  - Installing phpstan/phpdoc-parser (2.3.2): Extracting archive",
    "  - Installing phpdocumentor/reflection-common (2.2.0): Extracting archive",
    "  - Installing doctrine/deprecations (1.1.5): Extracting archive",
    "  - Installing phpdocumentor/type-resolver (1.12.0): Extracting archive",
    "  - Installing phpdocumentor/reflection-docblock (5.6.6): Extracting archive",
    "  - Installing psr/simple-cache (3.0.0): Extracting archive",
    "  - Installing symfony/finder (v8.0.5): Extracting archive",
    "  - Installing illuminate/contracts (v12.49.0): Extracting archive",
    "  - Installing spatie/laravel-package-tools (1.92.7): Extracting archive",
    "  - Installing symfony/polyfill-php85 (v1.33.0): Extracting archive",
    "  - Installing symfony/polyfill-php84 (v1.33.0): Extracting archive",
    "  - Installing symfony/polyfill-php83 (v1.33.0): Extracting archive",
    "  - Installing illuminate/collections (v12.49.0): Extracting archive",
    "  - Installing spatie/php-structure-discoverer (2.3.3): Extracting archive",
    "  - Installing symfony/polyfill-php80 (v1.33.0): Extracting archive",
    "  - Installing phpdocumentor/reflection (6.4.4): Extracting archive",
    "  - Installing spatie/laravel-data (4.19.1): Extracting archive",
    "Generating autoload files",
    "16 packages you are using are looking for funding.",
    "Use the `composer fund` command to find out more!",
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

confirm('Ready to rock?');

processLog(
    label: $commands[0][1],
    callback: function (Logger $logger) use ($commands) {

        $partial = [
            'this',
            'is',
            'a',
            'partial',
            'line',
            'that',
            'is',
            'longer',
            'than',
            'the',
            'this',
            'is',
            'a',
            'partial',
            'line',
            'that',
            'is',
            'longer',
            'than',
            'the',
            'this',
            'is',
            'a',
            'partial',
            'line',
            'that',
            'is',
            'longer',
            'than',
            'the',
            'this',
            'is',
            'a',
            'partial',
            'line',
            'that',
            'is',
            'longer',
            'than',
            'the',
            'BREAK',
            'other',
            'lines',
            'and',
            'should',
            'be',
            'split',
            'into',
            'multiple',
            'lines',
            'other',
            'lines',
            'and',
            'should',
            'be',
            'split',
            'into',
            'multiple',
            'lines',
        ];

        // $logger->line('START');

        // foreach ($partial as $line) {

        //     if ($line === 'BREAK') {
        //         $logger->commitPartialLine();
        //     } else {
        //         $logger->partialLine($line . ' ');
        //     }

        //     usleep(100_000);
        // }

        // $logger->line('END');

        // usleep(300_000);
        // return null;

        foreach ($commands as $index => $data) {
            [$output, $label, $message, $type] = $data;
            $logger->label($label);

            foreach ($output as $line) {
                $logger->line($line);
                usleep(100_000);
            }

            $logger->{$type}($message);
        }
    },
);

echo PHP_EOL;

info('We did it! So many dependencies.');

echo str_repeat(PHP_EOL, 5);
