<?php

use Laravel\Prompts\NotifyPrompt;

class TestableNotifyPrompt extends NotifyPrompt
{
    /** @var array<int, string> */
    public array $executedCommand = [];

    protected function execute(array $command): bool
    {
        $this->executedCommand = $command;

        return true;
    }
}

it('sets the title', function () {
    $prompt = new NotifyPrompt('Hello');

    expect($prompt->title)->toBe('Hello');
    expect($prompt->body)->toBe('');
});

it('sets the title and body', function () {
    $prompt = new NotifyPrompt('Hello', 'World');

    expect($prompt->title)->toBe('Hello');
    expect($prompt->body)->toBe('World');
});

it('sets macos options', function () {
    $prompt = new NotifyPrompt(
        title: 'Hello',
        body: 'World',
        subtitle: 'Sub',
        sound: 'Glass',
    );

    expect($prompt->subtitle)->toBe('Sub');
    expect($prompt->sound)->toBe('Glass');
});

it('sets linux options', function () {
    $prompt = new NotifyPrompt(
        title: 'Hello',
        body: 'World',
        icon: '/path/to/icon.png',
    );

    expect($prompt->icon)->toBe('/path/to/icon.png');
});

it('builds the correct macOS command', function () {
    $prompt = new TestableNotifyPrompt('Hello', 'World');

    $prompt->prompt();

    expect($prompt->executedCommand[0])->toBe('osascript');
    expect($prompt->executedCommand[1])->toBe('-e');
    expect($prompt->executedCommand[2])->toContain('display notification "World"');
    expect($prompt->executedCommand[2])->toContain('with title "Hello"');
    expect($prompt->executedCommand[2])->not->toContain('subtitle');
    expect($prompt->executedCommand[2])->not->toContain('sound name');
})->skip(PHP_OS_FAMILY !== 'Darwin', 'macOS only');

it('includes subtitle and sound in macOS command', function () {
    $prompt = new TestableNotifyPrompt(
        title: 'Hello',
        body: 'World',
        subtitle: 'Sub',
        sound: 'Glass',
    );

    $prompt->prompt();

    expect($prompt->executedCommand[2])->toContain('subtitle "Sub"');
    expect($prompt->executedCommand[2])->toContain('sound name "Glass"');
})->skip(PHP_OS_FAMILY !== 'Darwin', 'macOS only');
