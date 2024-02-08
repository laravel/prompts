<?php

use Laravel\Prompts\ConfirmPrompt;
use Laravel\Prompts\Exceptions\NonInteractiveValidationException;
use Laravel\Prompts\Key;
use Laravel\Prompts\PausePrompt;
use Laravel\Prompts\Prompt;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\pause;

it('continues', function () {
    Prompt::fake([Key::ENTER]);

    $result = pause('This is a fake content.');

    expect($result)->toBeTrue();
});

it('allows the title to be changed', function () {
    Prompt::fake([Key::ENTER]);

    $result = pause(
        'teste',
        'Leia com atenção!',
    );

    expect($result)->toBeTrue();

    Prompt::assertOutputContains('Leia com atenção!');
});


it('can fall back', function () {
    Prompt::fallbackWhen(true);

    PausePrompt::fallbackUsing(function (PausePrompt $prompt) {
        expect($prompt->body)->toBe('This is a fake content.')
            ->and($prompt->title)
            ->toBe('Warning...');

        return true;
    });

    $result = pause('This is a fake content.', 'Warning...', false);

    expect($result)->toBeTrue();
});

it('returns the default value when non-interactive', function () {
    Prompt::interactive(false);
    pause('This is a fake content.');
})->throws(NonInteractiveValidationException::class, 'Please, press ENTER to continue or Ctrl+C to cancel.');

it('validates the default value when non-interactive', function () {
    Prompt::interactive(false);

    pause(
        'This is a fake content.',
        required: true,
    );
})->throws(NonInteractiveValidationException::class, 'Required.');

