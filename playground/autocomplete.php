<?php

use function Laravel\Prompts\autocomplete;
use function Laravel\Prompts\select;

require __DIR__.'/../vendor/autoload.php';

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

$type = select(
    label: 'Which type?',
    options: [
        'standard' => 'Standard (array-based)',
        'progressive' => 'Progressive (closure-based)',
    ],
);

$path = match ($type) {
    'standard' => autocomplete(
        label: 'Which file?',
        options: $files,
        placeholder: 'E.g. app/Models/User.php',
        hint: 'Use tab to accept, up/down to cycle.',
    ),
    'progressive' => autocomplete(
        label: 'Which file?',
        options: function (string $value) use ($files): array {
            $matches = array_filter(
                $files,
                fn ($file) => str_starts_with(strtolower($file), strtolower($value)),
            );

            // Reveal just the next path segment beyond what's been typed
            return array_values(array_unique(array_map(function ($file) use ($value) {
                $remaining = substr($file, strlen($value));
                $nextSlash = strpos($remaining, '/');

                if ($nextSlash !== false) {
                    return $value.substr($remaining, 0, $nextSlash + 1);
                }

                return $file;
            }, $matches)));
        },
        placeholder: 'E.g. app/Models/User.php',
        hint: 'Use tab to accept, up/down to cycle.',
    ),
};

var_dump($path);

echo str_repeat(PHP_EOL, 5);
