<?php

use Laravel\Prompts\Elements\Element;

use function Laravel\Prompts\callout;
use function Laravel\Prompts\tree;

require __DIR__.'/../vendor/autoload.php';

tree([
    'app' => [
        'Models' => ['User.php', 'Order.php'],
        'Providers' => ['AppServiceProvider.php'],
    ],
    'config' => ['app.php', 'database.php'],
    'composer.json',
    'README.md',
]);

tree([
    'laravel/prompts' => [
        'symfony/console' => [
            'symfony/polyfill-mbstring',
            'symfony/service-contracts',
        ],
        'composer-runtime-api',
    ],
]);

tree([
    'Deployment steps' => [
        'Run `composer install --no-dev`',
        'Run `php artisan migrate --force`',
        'Restart the queue workers with `php artisan queue:restart`',
    ],
]);

callout(
    'Application Scaffolded',
    [
        'The following files were created for you:',
        Element::heading('Structure'),
        Element::tree([
            'app' => [
                'Http' => [
                    'Controllers' => ['PodcastsController.php'],
                    'Requests' => ['StorePodcastRequest.php'],
                ],
                'Models' => ['Podcast.php'],
            ],
            'database' => [
                'migrations' => ['2024_03_15_000000_create_podcasts_table.php'],
            ],
        ]),
        Element::heading('Next Steps'),
        Element::numberedList([
            'Run `php artisan migrate`',
            'Register the routes in `routes/web.php`',
        ]),
    ],
);
