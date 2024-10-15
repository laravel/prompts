<?php

use Laravel\Prompts\Exceptions\NonInteractiveValidationException;
use Laravel\Prompts\Key;
use Laravel\Prompts\MultiSelectPrompt;
use Laravel\Prompts\Prompt;

use function Laravel\Prompts\multiselect;

it('accepts an array of labels', function () {
    Prompt::fake([Key::DOWN, Key::SPACE, Key::DOWN, Key::SPACE, Key::ENTER]);

    $result = multiselect(
        label: 'What are your favorite colors?',
        options: [
            'Red',
            'Green',
            'Blue',
        ]
    );

    expect($result)->toBe(['Green', 'Blue']);

    Prompt::assertStrippedOutputDoesntContain('│ Red');
    Prompt::assertStrippedOutputContains('│ Green');
    Prompt::assertStrippedOutputContains('│ Blue');
});

it('accepts an array of keys and labels', function () {
    Prompt::fake([Key::DOWN, Key::SPACE, Key::DOWN, Key::SPACE, Key::ENTER]);

    $result = multiselect(
        label: 'What are your favorite colors?',
        options: [
            'red' => 'Red',
            'green' => 'Green',
            'blue' => 'Blue',
        ]
    );

    expect($result)->toBe(['green', 'blue']);
});

it('accepts an associate array with integer keys', function () {
    Prompt::fake([Key::DOWN, Key::SPACE, Key::DOWN, Key::SPACE, Key::ENTER]);

    $result = multiselect(
        label: 'What are your favorite colors?',
        options: [
            1 => 'Red',
            2 => 'Green',
            3 => 'Blue',
        ]
    );

    expect($result)->toBe([2, 3]);
});

it('accepts default values when the options are labels', function () {
    Prompt::fake([Key::ENTER]);

    $result = multiselect(
        label: 'What are your favorite colors?',
        options: [
            'Red',
            'Green',
            'Blue',
        ],
        default: ['Green']
    );

    expect($result)->toBe(['Green']);
});

it('accepts default values when the options are keys with labels', function () {
    Prompt::fake([Key::ENTER]);

    $result = multiselect(
        label: 'What are your favorite colors?',
        options: [
            'red' => 'Red',
            'green' => 'Green',
            'blue' => 'Blue',
        ],
        default: ['green']
    );

    expect($result)->toBe(['green']);
});

it('accepts collections', function () {
    Prompt::fake([Key::ENTER]);

    $result = multiselect(
        label: 'What are your favorite colors?',
        options: collect([
            'Red',
            'Green',
            'Blue',
        ]),
        default: collect(['Green'])
    );

    expect($result)->toBe(['Green']);
})->skip(! depends_on_collection());

it('transforms values', function () {
    Prompt::fake([Key::DOWN, Key::SPACE, Key::DOWN, Key::SPACE, Key::ENTER]);

    $result = multiselect(
        label: 'What are your favorite colors?',
        options: [
            'red' => 'Red',
            'green' => 'Green',
            'blue' => 'Blue',
        ],
        transform: fn ($value) => array_map('strtoupper', $value),
    );

    expect($result)->toBe(['GREEN', 'BLUE']);
});

it('validates', function () {
    Prompt::fake([Key::ENTER, Key::SPACE, Key::ENTER]);

    $result = multiselect(
        label: 'What are your favorite colors?',
        options: [
            'red' => 'Red',
            'green' => 'Green',
            'blue' => 'Blue',
        ],
        validate: fn ($values) => count($values) === 0 ? 'You must select at least one color.' : null
    );

    expect($result)->toBe(['red']);

    Prompt::assertOutputContains('You must select at least one color.');
});

it('can fall back', function () {
    Prompt::fallbackWhen(true);

    MultiSelectPrompt::fallbackUsing(function (MultiSelectPrompt $prompt) {
        expect($prompt->label)->toBe('What is your favorite color?');

        return ['Blue'];
    });

    $result = multiselect('What is your favorite color?', [
        'Red',
        'Green',
        'Blue',
    ]);

    expect($result)->toBe(['Blue']);
});

it('support emacs style key binding', function () {
    Prompt::fake([Key::CTRL_N, Key::SPACE, Key::CTRL_N, Key::SPACE, Key::ENTER]);

    $result = multiselect(
        label: 'What are your favorite colors?',
        options: [
            'red' => 'Red',
            'green' => 'Green',
            'blue' => 'Blue',
        ]
    );

    expect($result)->toBe(['green', 'blue']);
});

it('supports the home and end keys', function () {
    Prompt::fake([Key::END[0], Key::SPACE, Key::HOME[0], Key::SPACE, Key::ENTER]);

    $result = multiselect(
        label: 'What are your favorite colors?',
        options: [
            'red' => 'Red',
            'green' => 'Green',
            'blue' => 'Blue',
        ]
    );

    expect($result)->toBe(['blue', 'red']);
});

it('supports selecting all options', function () {
    Prompt::fake([Key::CTRL_A, Key::ENTER]);

    $result = multiselect(
        label: 'What are your favorite colors?',
        options: [
            'red' => 'Red',
            'green' => 'Green',
            'blue' => 'Blue',
        ]
    );

    expect($result)->toBe(['red', 'green', 'blue']);

    Prompt::fake([Key::CTRL_A, Key::CTRL_A, Key::ENTER]);

    $result = multiselect(
        label: 'What are your favorite colors?',
        options: [
            'red' => 'Red',
            'green' => 'Green',
            'blue' => 'Blue',
        ]
    );

    expect($result)->toBe([]);
});

it('returns an empty array when non-interactive', function () {
    Prompt::interactive(false);

    $result = multiselect('What is your favorite color?', [
        'Red',
        'Green',
        'Blue',
    ]);

    expect($result)->toBe([]);
});

it('returns the default value when non-interactive', function () {
    Prompt::interactive(false);

    $result = multiselect('What is your favorite color?', [
        'Red',
        'Green',
        'Blue',
    ], default: ['Green']);

    expect($result)->toBe(['Green']);
});

it('validates the default value when non-interactive', function () {
    Prompt::interactive(false);

    multiselect('What is your favorite color?', [
        'Red',
        'Green',
        'Blue',
    ], required: true);
})->throws(NonInteractiveValidationException::class, 'Required.');

it('supports custom validation', function () {
    Prompt::fake([Key::SPACE, Key::ENTER, Key::DOWN, Key::SPACE, Key::ENTER]);

    Prompt::validateUsing(function (Prompt $prompt) {
        expect($prompt)
            ->label->toBe('What are your favorite colors?')
            ->validate->toBe('in:green');

        return $prompt->validate === 'in:green' && ! in_array('green', $prompt->value()) ? 'And green?' : null;
    });

    $result = multiselect(
        label: 'What are your favorite colors?',
        options: [
            'red' => 'Red',
            'green' => 'Green',
            'blue' => 'Blue',
        ],
        validate: 'in:green',
    );

    expect($result)->toBe(['red', 'green']);

    Prompt::assertOutputContains('And green?');

    Prompt::validateUsing(fn () => null);
});
