<?php

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\intro;
use function Laravel\Prompts\multiselect;
use function Laravel\Prompts\note;
use function Laravel\Prompts\outro;
use function Laravel\Prompts\password;
use function Laravel\Prompts\select;
use function Laravel\Prompts\spin;
use function Laravel\Prompts\text;

require __DIR__ . '/../vendor/autoload.php';

// Laravel\Prompts\Prompt::theme('Clack');
// Laravel\Prompts\Prompt::theme('Terkelg');

intro('Welcome to Laravel');

$result = [
    'path' => text(
        message: 'Where should we create your project?',
        placeholder: './laravel',
        validate: function ($value) {
            if (!$value) return 'Please enter a path';
            if ($value[0] !== '.') return 'Please enter a relative path';
        }
    ),
    'password' => password(
        message: 'Provide a password',
        validate: function ($value) {
            if (!$value) return 'Please enter a password.';
            if (strlen($value) < 5) return 'Password should have at least 5 characters.';
        }
    ),
    'type' => select(
        message: 'Pick a project type',
        default: 'ts',
        options: [
            'ts' => 'TypeScript',
            'js' => 'JavaScript',
        ],
    ),
    'tools' => multiselect(
        message: 'Select additional tools.',
        default: ['pint', 'eslint'],
        options: [
            'pint' => 'Pint',
            'eslint' => 'ESLint',
            'prettier' => 'Prettier',
        ],
        validate: function ($values) {
            if (count($values) === 0) return 'Please select at least one tool.';
        }
    ),
    'install' => confirm(
        message: 'Install dependencies?',
        default: false,
    ),
];

if ($result['install']) {
    spin(fn () => sleep(3), 'Installing dependencies...');
}

note(<<<EOT
    Installation complete!

    To get started, run:

        cd {$result['path']}
        php artisan serve
    EOT);

outro('Happy coding!');

// var_dump($result);
