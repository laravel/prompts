<?php

use BadMethodCallException;
use Laravel\Prompts\Prompt;
use Laravel\Prompts\Support\Facades\Prompt as FacadePrompt;

it('Redirects to the real function.', function () {
    Prompt::fake();

    FacadePrompt::note('Dummy text');

    Prompt::assertOutputContains('Dummy text');
});

it('Throws BadMethodCallException when calling invalid function.', function () {
    FacadePrompt::notAFunction();
})->throws(BadMethodCallException::class, 'Call to undefined method ' . FacadePrompt::class . '::notAFunction()');

it('Can be instantiated and call functions.', function () {
    Prompt::fake();
    $prompt = new FacadePrompt();

    $prompt->note('Dummy text');

    Prompt::assertOutputContains('Dummy text');
});
