<?php

use Laravel\Prompts\P;

require __DIR__.'/../vendor/autoload.php';

P::intro('Welcome to Laravel');

$name = P::suggest(
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
    validate: fn ($value) => match (true) {
        ! $value => 'Please enter your name.',
        default => null,
    },
);

$path = P::text(
    label: 'Where should we create your project?',
    placeholder: 'E.g. ./laravel',
    validate: fn ($value) => match (true) {
        ! $value => 'Please enter a path',
        $value[0] !== '.' => 'Please enter a relative path',
        default => null,
    },
);

$password = P::password(
    label: 'Provide a password',
    validate: fn ($value) => match (true) {
        ! $value => 'Please enter a password.',
        strlen($value) < 5 => 'Password should have at least 5 characters.',
        default => null,
    },
);

$type = P::select(
    label: 'Pick a project type',
    default: 'ts',
    options: [
        'ts' => 'TypeScript',
        'js' => 'JavaScript',
    ],
);

$tools = P::multiselect(
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

$install = P::confirm(
    label: 'Install dependencies?',
);

if ($install) {
    P::spin(fn () => sleep(3), 'Installing dependencies...');
}

P::error('Error');
P::warning('Warning');
P::alert('Alert');

P::note(<<<EOT
    Installation complete!

    To get started, run:

        cd {$path}
        php artisan serve
    EOT);

P::outro('Happy coding!');

var_dump(compact('name', 'path', 'password', 'type', 'tools', 'install'));
