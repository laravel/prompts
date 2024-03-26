<?php

use Laravel\Prompts\Prompt;

use function Laravel\Prompts\spin;

it('renders a spinner while executing a callback and then returns the value', function () {
    Prompt::fake();

    $result = spin(function () {
        usleep(1000);

        return 'done';
    }, 'Running...');

    expect($result)->toBe('done');

    Prompt::assertOutputContains('Running...');
});

it('renders a spinner and displays a final message', function ($finalMessageHandler, $expectedMessage) {
    Prompt::fake();

    $result = spin(function () {
        usleep(1000);

        return 'result!';
    }, 'Running...', $finalMessageHandler);

    expect($result)->toBe('result!');

    Prompt::assertOutputContains('Running...');
    Prompt::assertOutputContains($expectedMessage);
})->with([
    'string' => ['All done!', 'All done!'],
    'closure' => [fn ($result) => "All done: {$result}", 'All done: result!'],
]);
