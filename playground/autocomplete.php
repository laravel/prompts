<?php

use function Laravel\Prompts\autocomplete;

require __DIR__ . '/../vendor/autoload.php';

$files = [
    'app/Http/Controllers/UserController.php',
    'app/Http/Controllers/PostController.php',
    'app/Http/Controllers/CommentController.php',
    'app/Http/Middleware/Authenticate.php',
    'app/Http/Middleware/TrustProxies.php',
    'app/Models/User.php',
    'app/Models/Post.php',
    'app/Models/Comment.php',
    'app/Providers/AppServiceProvider.php',
    'app/Providers/RouteServiceProvider.php',
    'config/app.php',
    'config/database.php',
    'config/logging.php',
    'database/migrations/create_users_table.php',
    'database/migrations/create_posts_table.php',
    'resources/views/welcome.blade.php',
    'resources/views/layouts/app.blade.php',
    'routes/web.php',
    'routes/api.php',
];

$path = autocomplete(
    label: 'Which file?',
    options: fn (string $value) => array_values(array_filter(
        $files,
        fn ($file) => str_starts_with(strtolower($file), strtolower($value)),
    )),
    placeholder: 'E.g. app/Models/User.php',
    hint: 'Use tab to accept a suggestion.',
);

var_dump($path);

echo str_repeat(PHP_EOL, 5);
