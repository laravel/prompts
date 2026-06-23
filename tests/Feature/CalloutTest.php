<?php

use Laravel\Prompts\Callout;
use Laravel\Prompts\Elements\Element;
use Laravel\Prompts\Prompt;

use function Laravel\Prompts\callout;

it('renders a callout with a label and string content', function () {
    Prompt::fake();

    callout('My Title', 'Hello, World!');

    Prompt::assertOutputContains('My Title');
    Prompt::assertOutputContains('Hello, World!');
});

it('renders a warning callout', function () {
    Prompt::fake();

    callout('Deprecation Notice', 'This will be removed.', 'warning');

    Prompt::assertOutputContains('⚠ Deprecation Notice');
    Prompt::assertOutputContains('This will be removed.');
});

it('renders an error callout', function () {
    Prompt::fake();

    callout('Connection Failed', 'Could not connect.', 'error');

    Prompt::assertOutputContains('⚠ Connection Failed');
    Prompt::assertOutputContains('Could not connect.');
});

it('renders a callout with info footer', function () {
    Prompt::fake();

    callout('Deploy', 'Deployed successfully.', info: 'deploy-id: abc123');

    Prompt::assertOutputContains('Deploy');
    Prompt::assertOutputContains('Deployed successfully.');
    Prompt::assertOutputContains('deploy-id: abc123');
});

it('renders a callout with a bulleted list', function () {
    Prompt::fake();

    callout('Summary', [
        'Changes made:',
        Element::bulletedList([
            'First item',
            'Second item',
        ]),
    ]);

    Prompt::assertOutputContains('Summary');
    Prompt::assertOutputContains('Changes made:');
    Prompt::assertStrippedOutputContains('· First item');
    Prompt::assertStrippedOutputContains('· Second item');
});

it('renders a callout with a numbered list', function () {
    Prompt::fake();

    callout('Steps', [
        'Follow these steps:',
        Element::numberedList([
            'Step one',
            'Step two',
            'Step three',
        ]),
    ]);

    Prompt::assertOutputContains('Steps');
    Prompt::assertStrippedOutputContains('1. Step one');
    Prompt::assertStrippedOutputContains('2. Step two');
    Prompt::assertStrippedOutputContains('3. Step three');
});

it('renders a callout with a key-value list', function () {
    Prompt::fake();

    callout('Details', [
        'Connection info:',
        Element::keyValueList([
            'Host' => '127.0.0.1',
            'Port' => '3306',
        ]),
    ]);

    Prompt::assertOutputContains('Details');
    Prompt::assertStrippedOutputContains('Host  127.0.0.1');
    Prompt::assertStrippedOutputContains('Port  3306');
});

it('renders a callout with a heading', function () {
    Prompt::fake();

    callout('Report', [
        'Summary of changes.',
        Element::heading('What Changed'),
        Element::bulletedList(['Item one']),
    ]);

    Prompt::assertOutputContains('Report');
    Prompt::assertOutputContains('What Changed');
    Prompt::assertOutputContains('Item one');
});

it('renders a callout with mixed content', function () {
    Prompt::fake();

    callout('Deployment', [
        'Deployed to production.',
        Element::heading('Changes'),
        Element::bulletedList(['Migration ran', 'Cache cleared']),
        Element::heading('Next Steps'),
        Element::numberedList(['Check health endpoint', 'Monitor errors']),
    ], info: 'deploy-id: xyz');

    Prompt::assertOutputContains('Deployment');
    Prompt::assertOutputContains('Deployed to production.');
    Prompt::assertOutputContains('Changes');
    Prompt::assertOutputContains('Migration ran');
    Prompt::assertOutputContains('Next Steps');
    Prompt::assertOutputContains('Check health endpoint');
    Prompt::assertOutputContains('deploy-id: xyz');
});

it('can fall back', function () {
    Prompt::fallbackWhen(true);

    Callout::fallbackUsing(function (Callout $callout) {
        expect($callout->label)->toBe('Test');
        expect($callout->content)->toBe('Content');

        return true;
    });

    $result = (new Callout('Test', 'Content'))->display();

    expect($result)->toBeNull();
});
