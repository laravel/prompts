<?php

use Laravel\Prompts\Key;
use Laravel\Prompts\PausePrompt;
use Laravel\Prompts\Prompt;

use function Laravel\Prompts\pause;

it('continues after enter', function () {
    Prompt::fake([Key::ENTER]);

    $result = pause();

    expect($result)->toBeTrue();

    Prompt::assertOutputContains('Press enter to continue...');
});

it('allows the message to be changed', function () {
    Prompt::fake([Key::ENTER]);

    $result = pause('Read and then press enter...');

    expect($result)->toBeTrue();

    Prompt::assertOutputContains('Read and then press enter...');
});

it('can fall back', function () {
    Prompt::fallbackWhen(true);

    PausePrompt::fallbackUsing(function (PausePrompt $prompt) {
        expect($prompt->message)->toBe('Press enter to continue...');

        return true;
    });

    $result = pause();

    expect($result)->toBeTrue();
});

it('does not render when non-interactive', function () {
    Prompt::fake();
    Prompt::interactive(false);

    $result = pause('This should not be rendered');

    expect($result)->toBeFalse();

    Prompt::assertOutputDoesntContain('This should not be rendered');
});
