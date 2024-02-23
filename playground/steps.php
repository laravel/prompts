<?php

use function Laravel\Prompts\alert;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\error;
use function Laravel\Prompts\intro;
use function Laravel\Prompts\multiselect;
use function Laravel\Prompts\note;
use function Laravel\Prompts\outro;
use function Laravel\Prompts\password;
use function Laravel\Prompts\select;
use function Laravel\Prompts\spin;
use function Laravel\Prompts\suggest;
use function Laravel\Prompts\text;
use function Laravel\Prompts\warning;

require __DIR__.'/../vendor/autoload.php';

$responses = steps()
    ->add(fn () => intro('Welcome to Laravel'), revert: false)
    ->add(fn () => suggest(
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
    ))
    ->add(fn () => text(
        label: 'Where should we create your project?',
        placeholder: 'E.g. ./laravel',
        validate: fn ($value) => match (true) {
            ! $value => 'Please enter a path',
            $value[0] !== '.' => 'Please enter a relative path',
            default => null,
        },
    ))
    ->add(fn () => password(
        label: 'Provide a password',
        validate: fn ($value) => match (true) {
            ! $value => 'Please enter a password.',
            strlen($value) < 5 => 'Password should have at least 5 characters.',
            default => null,
        },
    ), revert: false)
    ->add(fn () => select(
        label: 'Pick a project type',
        default: 'ts',
        options: [
            'ts' => 'TypeScript',
            'js' => 'JavaScript',
        ],
    ))
    ->add(fn () => multiselect(
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
    ))
    ->add(function () {
        $install = confirm(
            label: 'Install dependencies?',
        );

        if ($install) {
            spin(fn () => sleep(3), 'Installing dependencies...');
        }

        return $install;
    }, revert: function () {
        spin(fn () => sleep(3), 'Uninstalling...');
    })
    ->add(fn ($responses) => note(<<<EOT
    Installation complete!

    To get started, run:

        cd {$responses[2]}
        php artisan serve
    EOT))
    ->run();

var_dump($responses);
