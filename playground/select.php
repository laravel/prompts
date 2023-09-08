<?php

use function Laravel\Prompts\select;

require __DIR__.'/../vendor/autoload.php';

$role = select(
    label: 'Where are you from?',
    options: [
        'argentina' => 'Argentina',
        'australia' => 'Australia',
        'belgium' => 'Belgium',
        'brazil' => 'Brazil',
        'canada' => 'Canada',
        'chile' => 'Chile',
        'china' => 'China',
        'colombia' => 'Colombia',
        'egypt' => 'Egypt',
        'france' => 'France',
        'germany' => 'Germany',
        'india' => 'India',
        'italy' => 'Italy',
        'japan' => 'Japan',
        'kenya' => 'Kenya',
        'mexico' => 'Mexico',
        'morocco' => 'Morocco',
        'nigeria' => 'Nigeria',
        'new-zealand' => 'New Zealand',
        'portugal' => 'Portugal',
        'south-africa' => 'South Africa',
        'south-korea' => 'South Korea',
        'spain' => 'Spain',
        'switzerland' => 'Switzerland',
        'united-kingdom' => 'United Kingdom',
        'united-states' => 'United States',
    ],
    default: 'france',
    validate: fn ($value) => match ($value) {
        'spain' => 'Spain is not available yet.',
        default => null
    },
    hint: 'The country will determine the currency and the timezone of the user.',
);

var_dump($role);

echo str_repeat(PHP_EOL, 5);
