<?php

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\form;
use function Laravel\Prompts\note;
use function Laravel\Prompts\spin;

require __DIR__.'/../vendor/autoload.php';

$responses = form()
    ->intro('Welcome to Laravel')
    ->suggest(
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
    )
    ->select('Which scaffolding project would you like?', ['Breeze', 'Jetstream', 'None'], required: true, name: 'scaffolding')
    ->nested(
        fn (array $responses) => form()
            ->note("{$responses['scaffolding']} selected")
            ->confirm('Would you like to install dark mode?')
            ->select('Which stack would you like?', ['Blade', 'Vue', 'React']),
        when: fn (array $responses) => $responses['scaffolding'] === 'Breeze',
        name: 'breeze-stack',
    )
    ->nested(
        form()
            ->confirm('Would you like to install teams support?')
            ->confirm('Would you like to support dark mode?')
            ->select('Which stack would you like', ['Inertia', 'Livewire']),
        when: fn (array $responses) => $responses['scaffolding'] === 'Jetstream',
        name: 'jet-stack',
    )
    ->text(
        label: 'Where should we create your project?',
        placeholder: 'E.g. ./laravel',
        validate: fn ($value) => match (true) {
            ! $value => 'Please enter a path',
            $value[0] !== '.' => 'Please enter a relative path',
            default => null,
        },
        name: 'path'
    )
    ->textarea('Describe your project')
    ->pause()
    ->submit();

$moreResponses = form()
    ->password(
        label: 'Provide a password',
        validate: fn ($value) => match (true) {
            ! $value => 'Please enter a password.',
            strlen($value) < 5 => 'Password should have at least 5 characters.',
            default => null,
        },
    )
    ->select(
        label: 'Pick a project type',
        default: 'ts',
        options: [
            'ts' => 'TypeScript',
            'js' => 'JavaScript',
        ],
    )
    ->multiselect(
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
    )
    ->add(function () {
        $install = confirm(
            label: 'Install dependencies?',
        );

        if ($install) {
            spin(fn () => sleep(3), 'Installing dependencies...');
        }

        return $install;
    }, name: 'install')
    ->confirm('Finish installation?')
    ->add(fn ($responses) => note(<<<EOT
    Installation complete!

    To get started, run:

        cd {$responses['path']}
        php artisan serve
    EOT
    ))
    ->submit();

var_dump($responses, $moreResponses);
