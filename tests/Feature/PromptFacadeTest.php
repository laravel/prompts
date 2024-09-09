<?php

use BadMethodCallException;
use Laravel\Prompts\Prompt;
use Laravel\Prompts\Support\Facades\Prompt as FacadePrompt;

it('Redirects to the real function', function () {
    Prompt::fake();

    FacadePrompt::note('Dummy test');

    Prompt::assertOutputContains('Dummy test');
});

it('Throws BadMethodCallException when calling invalid function', function () {
    FacadePrompt::notAFunction();
})->throws(BadMethodCallException::class, 'Call to undefined method ' . FacadePrompt::class . '::notAFunction()');
