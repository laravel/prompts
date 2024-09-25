<?php

use Laravel\Prompts\Exceptions\NonInteractiveValidationException;
use Laravel\Prompts\Key;
use Laravel\Prompts\Prompt;
use Laravel\Prompts\SelectPrompt;

use function Laravel\Prompts\select;

it('accepts an array of labels', function () {
    Prompt::fake([Key::DOWN, Key::ENTER]);

    $result = select(
        label: 'What is your favorite color?',
        options: [
            'Red',
            'Green',
            'Blue',
        ]
    );

    expect($result)->toBe('Green');
});

it('accepts an array of keys and labels', function () {
    Prompt::fake([Key::DOWN, Key::ENTER]);

    $result = select(
        label: 'What is your favorite color?',
        options: [
            'red' => 'Red',
            'green' => 'Green',
            'blue' => 'Blue',
        ]
    );

    expect($result)->toBe('green');
});

it('accepts an associate array with integer keys', function () {
    Prompt::fake([Key::DOWN, Key::ENTER]);

    $result = select(
        label: 'What is your favorite color?',
        options: [
            1 => 'Red',
            2 => 'Green',
            3 => 'Blue',
        ]
    );

    expect($result)->toBe(2);
});

it('accepts a collection', function () {
    Prompt::fake([Key::DOWN, Key::ENTER]);

    $result = select(
        label: 'What is your favorite color?',
        options: collect([
            'Red',
            'Green',
            'Blue',
        ]),
    );

    expect($result)->toBe('Green');
})->skip(! depends_on_collection());

it('accepts default values when the options are labels', function () {
    Prompt::fake([Key::ENTER]);

    $result = select(
        label: 'What is your favorite color?',
        options: [
            'Red',
            'Green',
            'Blue',
        ],
        default: 'Green'
    );

    expect($result)->toBe('Green');
});

it('accepts default values when the options are keys with labels', function () {
    Prompt::fake([Key::ENTER]);

    $result = select(
        label: 'What is your favorite color?',
        options: [
            'red' => 'Red',
            'green' => 'Green',
            'blue' => 'Blue',
        ],
        default: 'green'
    );

    expect($result)->toBe('green');
});

it('transforms values', function () {
    Prompt::fake([Key::DOWN, Key::ENTER]);

    $result = select(
        label: 'What is your favorite color?',
        options: [
            'Red',
            'Green',
            'Blue',
        ],
        transform: fn ($value) => strtolower($value),
    );

    expect($result)->toBe('green');
});

it('validates', function () {
    Prompt::fake([Key::ENTER, Key::DOWN, Key::ENTER]);

    $result = select(
        label: 'What is your favorite color?',
        options: [
            'red' => 'Red',
            'green' => 'Green',
            'blue' => 'Blue',
        ],
        validate: fn ($value) => $value === 'red' ? 'You can\'t choose red.' : null
    );

    expect($result)->toBe('green');

    Prompt::assertOutputContains('You can\'t choose red.');
});

it('can fall back', function () {
    Prompt::fallbackWhen(true);

    SelectPrompt::fallbackUsing(function (SelectPrompt $prompt) {
        expect($prompt->label)->toBe('What is your favorite color?');

        return 'Blue';
    });

    $result = select('What is your favorite color?', [
        'Red',
        'Green',
        'Blue',
    ]);

    expect($result)->toBe('Blue');
});

it('centers the default value when it\'s not visible', function () {
    Prompt::fake([Key::ENTER]);

    $result = select(
        label: 'What is your favorite color?',
        options: [
            'Red',
            'Green',
            'Blue',
            'Yellow',
            'Orange',
            'Purple',
            'Pink',
            'Brown',
            'Black',
        ],
        default: 'Purple',
        scroll: 3
    );

    expect($result)->toBe('Purple');

    Prompt::assertOutputContains('Orange');
    Prompt::assertOutputContains('Purple');
    Prompt::assertOutputContains('Pink');
});

it('scrolls to the bottom when the default value is near the end', function (int $scroll, array $outputContains) {
    Prompt::fake([Key::ENTER]);

    $result = select(
        label: 'What is your favorite color?',
        options: [
            'Red',
            'Green',
            'Blue',
            'Yellow',
            'Orange',
            'Purple',
            'Pink',
            'Brown',
            'Black',
        ],
        default: 'Brown',
        scroll: $scroll
    );

    expect($result)->toBe('Brown');

    foreach ($outputContains as $output) {
        Prompt::assertOutputContains($output);
    }
})->with([
    'odd' => [
        'scroll' => 5,
        'outputContains' => [
            'Orange',
            'Purple',
            'Pink',
            'Brown',
            'Black',
        ],
    ],
    'even' => [
        'scroll' => 6,
        'outputContains' => [
            'Yellow',
            'Orange',
            'Purple',
            'Pink',
            'Brown',
            'Black',
        ],
    ],
]);

it('support emacs style key binding', function () {
    Prompt::fake([Key::CTRL_N, Key::CTRL_P, Key::CTRL_N, Key::ENTER]);

    $result = select(
        label: 'What is your favorite color?',
        options: [
            'Red',
            'Green',
            'Blue',
        ]
    );

    expect($result)->toBe('Green');
});

it('supports the home key', function () {
    Prompt::fake([Key::HOME[0], Key::ENTER]);

    $result = select(
        label: 'What is your favorite color?',
        options: [
            'Red',
            'Green',
            'Blue',
        ],
        default: 'Blue'
    );

    expect($result)->toBe('Red');
});

it('supports the end key', function () {
    Prompt::fake([Key::END[0], Key::ENTER]);

    $result = select(
        label: 'What is your favorite color?',
        options: [
            'Red',
            'Green',
            'Blue',
        ],
    );

    expect($result)->toBe('Blue');
});

it('allows empty strings', function () {
    Prompt::fake([Key::ENTER]);

    $result = select(
        label: 'What is your favorite color?',
        options: [
            '' => 'Empty',
            'not-empty' => 'Not empty',
        ],
    );

    expect($result)->toBe('');
});

it('fails when there is no default in non-interactive mode', function () {
    Prompt::interactive(false);

    select('What is your favorite color?', [
        'Red',
        'Green',
        'Blue',
    ]);
})->throws(NonInteractiveValidationException::class, 'Required.');

it('returns the default value when non-interactive', function () {
    Prompt::interactive(false);

    $result = select('What is your favorite color?', [
        'Red',
        'Green',
        'Blue',
    ], default: 'Green');

    expect($result)->toBe('Green');
});

it('validates the default value when non-interactive', function () {
    Prompt::interactive(false);

    select(
        label: 'What is your favorite color?',
        options: [
            'None',
            'Red',
            'Green',
            'Blue',
        ],
        default: 'None',
        validate: fn ($value) => $value === 'None' ? 'Required.' : null,
    );
})->throws(NonInteractiveValidationException::class, 'Required.');

it('Allows the required validation message to be customised when non-interactive', function () {
    Prompt::interactive(false);

    select(
        label: 'What is your favorite color?',
        options: [
            'Red',
            'Green',
            'Blue',
        ],
        required: 'The color is required.',
    );
})->throws(NonInteractiveValidationException::class, 'The color is required.');

it('supports custom validation', function () {
    Prompt::fake([Key::ENTER, Key::DOWN, Key::ENTER]);

    Prompt::validateUsing(function (Prompt $prompt) {
        expect($prompt)
            ->label->toBe('What is your favorite color?')
            ->validate->toBe('in:green');

        return $prompt->validate === 'in:green' && $prompt->value() != 'green' ? 'Please choose green.' : null;
    });

    $result = select(
        label: 'What is your favorite color?',
        options: [
            'red' => 'Red',
            'green' => 'Green',
            'blue' => 'Blue',
        ],
        validate: 'in:green',
    );

    expect($result)->toBe('green');

    Prompt::assertOutputContains('Please choose green.');

    Prompt::validateUsing(fn () => null);
});
