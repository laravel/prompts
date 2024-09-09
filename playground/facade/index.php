<?php

use Laravel\Prompts\Support\Facades\Prompt;

require __DIR__ . '/../../vendor/autoload.php';

Prompt::intro('Welcome to Laravel');

$name = Prompt::suggest(
    label: 'What is your name?',
    placeholder: 'E.g. Taylor Otwell',
    options: [
        'Dries Vints',
        'Guus Leeuw',
        'James Brooks',
        'Jess Archer',
        'Joe Dixon',
        'Mior Muhammad Zaki Mior Khairuddin',
        'Nuno Maduro',
        'Taylor Otwell',
        'Tim MacDonald',
    ],
    validate: fn($value) => match (true) {
        ! $value => 'Please enter your name.',
        default => null,
    },
);

$path = Prompt::text(
    label: 'Where should we create your project?',
    placeholder: 'E.g. ./laravel',
    validate: fn($value) => match (true) {
        ! $value => 'Please enter a path',
        $value[0] !== '.' => 'Please enter a relative path',
        default => null,
    },
);

$password = Prompt::password(
    label: 'Provide a password',
    validate: fn($value) => match (true) {
        ! $value => 'Please enter a password.',
        strlen($value) < 5 => 'Password should have at least 5 characters.',
        default => null,
    },
);

$type = Prompt::select(
    label: 'Pick a project type',
    default: 'ts',
    options: [
        'ts' => 'TypeScript',
        'js' => 'JavaScript',
    ],
);

$tools = Prompt::multiselect(
    label: 'Select additional tools.',
    default: ['pint', 'eslint'],
    options: [
        'pint' => 'Pint',
        'eslint' => 'ESLint',
        'prettier' => 'Prettier',
    ],
    validate: function ($values) {
        if (count($values) === 0) {
            return 'Please select at least one tool.';
        }
    }
);

$install = Prompt::confirm(
    label: 'Install dependencies?',
);

if ($install) {
    Prompt::spin(fn() => sleep(3), 'Installing dependencies...');
}

Prompt::error('Error');
Prompt::warning('Warning');
Prompt::alert('Alert');

Prompt::note(<<<EOT
    Installation complete!

    To get started, run:

        cd {$path}
        php artisan serve
    EOT);

Prompt::outro('Happy coding!');

var_dump(compact('name', 'path', 'password', 'type', 'tools', 'install'));
