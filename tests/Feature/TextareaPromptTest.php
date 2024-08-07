<?php

use Laravel\Prompts\Exceptions\NonInteractiveValidationException;
use Laravel\Prompts\Key;
use Laravel\Prompts\Prompt;
use Laravel\Prompts\TextareaPrompt;

use function Laravel\Prompts\textarea;

it('returns the input', function () {
    Prompt::fake(['J', 'e', 's', 's', Key::ENTER, 'J', 'o', 'e', Key::CTRL_D]);

    $result = textarea(label: 'What is your name?');

    expect($result)->toBe("Jess\nJoe");
});

it('accepts a default value', function () {
    Prompt::fake([Key::CTRL_D]);

    $result = textarea(
        label: 'What is your name?',
        default: "Jess\nJoe"
    );

    expect($result)->toBe("Jess\nJoe");
});

it('transforms values', function () {
    Prompt::fake([Key::SPACE, 'J', 'e', 's', 's', Key::SPACE, Key::CTRL_D]);

    $result = textarea(
        label: 'What is your name?',
        transform: fn ($value) => trim($value),
    );

    expect($result)->toBe('Jess');
});

it('validates', function () {
    Prompt::fake(['J', 'e', 's', Key::CTRL_D, 's', Key::CTRL_D]);

    $result = textarea(
        label: 'What is your name?',
        validate: fn ($value) => $value !== 'Jess' ? 'Invalid name.' : '',
    );

    expect($result)->toBe('Jess');

    Prompt::assertOutputContains('Invalid name.');
});

it('cancels', function () {
    Prompt::fake([Key::CTRL_C]);

    textarea(label: 'What is your name?');

    Prompt::assertOutputContains('Cancelled.');
});

test('the backspace key removes a character', function () {
    Prompt::fake(['J', 'e', 'z', Key::BACKSPACE, 's', 's', Key::CTRL_D]);

    $result = textarea(label: 'What is your name?');

    expect($result)->toBe('Jess');
});

test('the delete key removes a character', function () {
    Prompt::fake(['J', 'e', 'z', Key::LEFT, Key::DELETE, 's', 's', Key::CTRL_D]);

    $result = textarea(label: 'What is your name?');

    expect($result)->toBe('Jess');
});

it('can fall back', function () {
    Prompt::fallbackWhen(true);

    TextareaPrompt::fallbackUsing(function (TextareaPrompt $prompt) {
        expect($prompt->label)->toBe('What is your name?');

        return 'result';
    });

    $result = textarea('What is your name?');

    expect($result)->toBe('result');
});

it('supports emacs style key bindings', function () {
    Prompt::fake(['J', 'z', 'e', Key::CTRL_B, Key::CTRL_H, key::CTRL_F, 's', 's', Key::CTRL_D]);

    $result = textarea(label: 'What is your name?');

    expect($result)->toBe('Jess');
});

it('moves to the beginning and end of line', function () {
    Prompt::fake(['e', 's', Key::HOME[0], 'J', KEY::END[0], 's', Key::CTRL_D]);

    $result = textarea(label: 'What is your name?');

    expect($result)->toBe('Jess');
});

it('moves up and down lines', function () {
    Prompt::fake([
        'e', 's', 's', Key::ENTER, 'o', 'e',
        KEY::UP_ARROW, KEY::LEFT_ARROW, Key::LEFT_ARROW,
        'J', KEY::DOWN_ARROW, KEY::LEFT_ARROW, 'J', Key::CTRL_D,
    ]);

    $result = textarea(label: 'What is your name?');

    expect($result)->toBe("Jess\nJoe");
});

it('moves to the start of the line if up is pressed twice on the first line', function () {
    Prompt::fake([
        'e', 's', 's', Key::ENTER, 'J', 'o', 'e',
        KEY::UP_ARROW, KEY::UP_ARROW, 'J', Key::CTRL_D,
    ]);

    $result = textarea(label: 'What is your name?');

    expect($result)->toBe("Jess\nJoe");
});

it('moves to the end of the line if down is pressed twice on the last line', function () {
    Prompt::fake([
        'J', 'e', 's', 's', Key::ENTER, 'J', 'o',
        KEY::UP_ARROW, KEY::UP_ARROW, Key::DOWN_ARROW,
        Key::DOWN_ARROW, 'e', Key::CTRL_D,
    ]);

    $result = textarea(label: 'What is your name?');

    expect($result)->toBe("Jess\nJoe");
});

it('can move back to the last line when it is empty', function () {
    Prompt::fake([
        'J', 'e', 's', 's', Key::ENTER,
        Key::UP, Key::DOWN,
        'J', 'o', 'e',
        Key::CTRL_D,
    ]);

    $result = textarea(label: 'What is your name?');

    expect($result)->toBe("Jess\nJoe");
});

it('returns an empty string when non-interactive', function () {
    Prompt::interactive(false);

    $result = textarea('What is your name?');

    expect($result)->toBe('');
});

it('returns the default value when non-interactive', function () {
    Prompt::interactive(false);

    $result = textarea('What is your name?', default: 'Taylor');

    expect($result)->toBe('Taylor');
});

it('validates the default value when non-interactive', function () {
    Prompt::interactive(false);

    textarea('What is your name?', required: true);
})->throws(NonInteractiveValidationException::class, 'Required.');

it('correctly handles ascending line lengths', function () {
    Prompt::fake([
        'a', Key::ENTER,
        'b', 'c', Key::ENTER,
        'd', 'e', 'f',
        Key::UP,
        Key::UP,
        Key::DOWN,
        'g',
        Key::CTRL_D,
    ]);

    $result = textarea(label: 'What is your name?');

    expect($result)->toBe("a\nbgc\ndef");
});

it('correctly handles descending line lengths', function () {
    Prompt::fake([
        'a', 'b', 'c', Key::ENTER,
        'd', 'e', Key::ENTER,
        'f',
        Key::UP,
        Key::UP,
        Key::RIGHT,
        Key::RIGHT,
        Key::DOWN,
        'g',
        Key::CTRL_D,
    ]);

    $result = textarea(label: 'What is your name?');

    expect($result)->toBe("abc\ndeg\nf");
});

it('correctly handles multi-byte strings for the down arrow', function () {
    Prompt::fake([
        'ａ', 'ｂ', Key::ENTER,
        'ｃ', 'ｄ', 'ｅ', 'ｆ', Key::ENTER,
        'ｇ', 'ｈ', 'ｉ', 'j', 'k', 'l', 'm', 'n', 'n', 'o', 'p', 'q', 'r', 's', Key::ENTER,
        't', 'u', 'v', 'w', 'x', 'y', 'z',
        Key::UP,
        Key::UP,
        Key::UP,
        Key::UP,
        Key::RIGHT,
        Key::DOWN,
        'y', 'o',
        Key::CTRL_D,
    ]);

    $result = textarea(label: 'What is your name?');

    expect($result)->toBe(
        "ａｂ\nｃyoｄｅｆ\nｇｈｉjklmnnopqrs\ntuvwxyz"
    );
});

it('correctly handles multi-byte strings for the up arrow', function () {
    Prompt::fake([
        'ａ', 'ｂ', Key::ENTER,
        'ｃ', 'ｄ', 'ｅ', 'ｆ', Key::ENTER,
        'ｇ', 'ｈ', 'ｉ', 'j', 'k', 'l', 'm', 'n', 'n', 'o', 'p', 'q', 'r', 's', Key::ENTER,
        't', 'u', 'v', 'w', 'x', 'y', 'z',
        Key::UP,
        Key::UP,
        Key::UP,
        Key::UP,
        Key::RIGHT,
        Key::DOWN,
        Key::UP,
        'y', 'o',
        Key::CTRL_D,
    ]);

    $result = textarea(label: 'What is your name?');

    expect($result)->toBe(
        "ａyoｂ\nｃｄｅｆ\nｇｈｉjklmnnopqrs\ntuvwxyz"
    );
});
