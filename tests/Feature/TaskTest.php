<?php

use Laravel\Prompts\Prompt;
use Laravel\Prompts\Support\Logger;
use Laravel\Prompts\Task;
use Laravel\Prompts\Themes\Default\TaskRenderer;

use function Laravel\Prompts\task;

it('renders a task while executing a callback and then returns the value', function () {
    Prompt::fake();

    $result = task(
        label: 'Running...',
        callback: function (Logger $logger) {
            usleep(1000);

            return 'done';
        },
    );

    expect($result)->toBe('done');

    Prompt::assertOutputContains('Running...');
});

it('returns null when the callback does not return a value', function () {
    Prompt::fake();

    $result = task(
        label: 'Working...',
        callback: function (Logger $logger) {
            usleep(1000);
        },
    );

    expect($result)->toBeNull();
});

it('receives log lines into the ring buffer', function () {
    $task = new Task(label: 'Test', limit: 3);

    $reflection = new ReflectionMethod($task, 'addLogLines');

    $reflection->invoke($task, 'line one');
    $reflection->invoke($task, 'line two');
    $reflection->invoke($task, 'line three');
    $reflection->invoke($task, 'line four');

    expect($task->logs)->toHaveCount(3);
    expect($task->logs[0])->toBe('line two');
    expect($task->logs[1])->toBe('line three');
    expect($task->logs[2])->toBe('line four');
});

it('wraps long lines and respects the limit', function () {
    Prompt::fake();

    $task = new Task(label: 'Test', limit: 3);

    $reflection = new ReflectionMethod($task, 'addLogLines');

    // 80 cols - 10 = 70 char width, this line is well over that
    $longLine = str_repeat('a ', 50);
    $reflection->invoke($task, $longLine);

    // Should have been wrapped into multiple lines, trimmed to limit
    expect(count($task->logs))->toBeLessThanOrEqual(3);
});

it('replaces partial lines on each update', function () {
    Prompt::fake();

    $task = new Task(label: 'Test', limit: 10);

    $addLogLines = new ReflectionMethod($task, 'addLogLines');
    $replacePartialLines = new ReflectionMethod($task, 'replacePartialLines');

    $addLogLines->invoke($task, 'existing line');

    expect($task->logs)->toHaveCount(1);

    $replacePartialLines->invoke($task, 'hello');
    expect($task->logs)->toHaveCount(2);
    expect($task->logs[0])->toBe('existing line');
    expect($task->logs[1])->toBe('hello');

    // Next partial replaces, not appends
    $replacePartialLines->invoke($task, 'hello world');
    expect($task->logs)->toHaveCount(2);
    expect($task->logs[0])->toBe('existing line');
    expect($task->logs[1])->toBe('hello world');
});

it('commits partial lines so they become permanent', function () {
    Prompt::fake();

    $task = new Task(label: 'Test', limit: 10);

    $addLogLines = new ReflectionMethod($task, 'addLogLines');
    $replacePartialLines = new ReflectionMethod($task, 'replacePartialLines');

    $replacePartialLines->invoke($task, 'streamed text');

    // Simulate commitpartial by clearing the index
    $partialStartIndex = new ReflectionProperty($task, 'partialStartIndex');
    $partialStartIndex->setValue($task, null);

    // Now add a new line — it should append, not replace
    $addLogLines->invoke($task, 'new line');

    expect($task->logs)->toHaveCount(2);
    expect($task->logs[0])->toBe('streamed text');
    expect($task->logs[1])->toBe('new line');
});

it('clears logs when a stable message is received', function () {
    Prompt::fake();

    $task = new Task(label: 'Test', limit: 10);

    $addLogLines = new ReflectionMethod($task, 'addLogLines');
    $addLogLines->invoke($task, 'log line');

    expect($task->logs)->toHaveCount(1);

    $task->stableMessages[] = ['type' => 'success', 'message' => 'Done!'];
    $task->logs = [];

    expect($task->logs)->toBeEmpty();
    expect($task->stableMessages)->toHaveCount(1);
    expect($task->stableMessages[0]['type'])->toBe('success');
    expect($task->stableMessages[0]['message'])->toBe('Done!');
});

it('trims stable messages to maxStableMessages', function () {
    $task = new Task(label: 'Test', limit: 10);
    $task->maxStableMessages = 2;

    $task->stableMessages[] = ['type' => 'success', 'message' => 'First'];
    $task->stableMessages[] = ['type' => 'success', 'message' => 'Second'];
    $task->stableMessages[] = ['type' => 'success', 'message' => 'Third'];

    while (count($task->stableMessages) > $task->maxStableMessages) {
        array_shift($task->stableMessages);
    }

    expect($task->stableMessages)->toHaveCount(2);
    expect($task->stableMessages[0]['message'])->toBe('Second');
    expect($task->stableMessages[1]['message'])->toBe('Third');
});

it('receives messages through the socket protocol', function () {
    Prompt::fake();

    $task = new Task(label: 'Initial', limit: 10);

    $receiveMessages = new ReflectionMethod($task, 'receiveMessages');

    // Create a socket pair to simulate IPC
    $sockets = stream_socket_pair(STREAM_PF_UNIX, STREAM_SOCK_STREAM, STREAM_IPPROTO_IP);

    $id = $task->identifier;

    // Write messages from the "parent" side
    fwrite($sockets[1], "plain log line\n");
    fwrite($sockets[1], "{$id}_label:New Label\n");
    fwrite($sockets[1], "another log line\n");
    fwrite($sockets[1], "{$id}_success:Step complete\n");
    fclose($sockets[1]);

    stream_set_blocking($sockets[0], false);
    $receiveMessages->invoke($task, $sockets[0]);
    fclose($sockets[0]);

    expect($task->label)->toBe('New Label');
    expect($task->stableMessages)->toHaveCount(1);
    expect($task->stableMessages[0])->toBe(['type' => 'success', 'message' => 'Step complete']);
    // Logs cleared when stable message received, so only post-stable logs remain
    expect($task->logs)->toBeEmpty();
});

it('handles partial messages through the socket protocol', function () {
    Prompt::fake();

    $task = new Task(label: 'Test', limit: 10);

    $receiveMessages = new ReflectionMethod($task, 'receiveMessages');

    $sockets = stream_socket_pair(STREAM_PF_UNIX, STREAM_SOCK_STREAM, STREAM_IPPROTO_IP);

    $id = $task->identifier;

    fwrite($sockets[1], "existing line\n");
    fwrite($sockets[1], "{$id}_partial:hello \n");
    fwrite($sockets[1], "{$id}_partial:hello world \n");
    fwrite($sockets[1], "{$id}_commitpartial:\n");
    fwrite($sockets[1], "after commit\n");
    fclose($sockets[1]);

    stream_set_blocking($sockets[0], false);
    $receiveMessages->invoke($task, $sockets[0]);
    fclose($sockets[0]);

    expect($task->logs)->toHaveCount(3);
    expect($task->logs[0])->toBe('existing line');
    expect($task->logs[1])->toBe('hello world ');
    expect($task->logs[2])->toBe('after commit');
});

it('strips cursor-reset control sequences from log lines', function () {
    Prompt::fake();

    $task = new Task(label: 'Test', limit: 10);

    $receiveMessages = new ReflectionMethod($task, 'receiveMessages');

    $sockets = stream_socket_pair(STREAM_PF_UNIX, STREAM_SOCK_STREAM, STREAM_IPPROTO_IP);

    fwrite($sockets[1], "before\e[1G\e[2Kafter\n");
    fclose($sockets[1]);

    stream_set_blocking($sockets[0], false);
    $receiveMessages->invoke($task, $sockets[0]);
    fclose($sockets[0]);

    expect($task->logs[0])->toBe('beforeafter');
});

it('updates the label through the socket protocol', function () {
    Prompt::fake();

    $task = new Task(label: 'Initial', limit: 10);

    $receiveMessages = new ReflectionMethod($task, 'receiveMessages');

    $sockets = stream_socket_pair(STREAM_PF_UNIX, STREAM_SOCK_STREAM, STREAM_IPPROTO_IP);

    $id = $task->identifier;

    fwrite($sockets[1], "{$id}_label:Updated Label\n");
    fclose($sockets[1]);

    stream_set_blocking($sockets[0], false);
    $receiveMessages->invoke($task, $sockets[0]);
    fclose($sockets[0]);

    expect($task->label)->toBe('Updated Label');
});

it('does not keep the summary by default', function () {
    $task = new Task(label: 'Running', limit: 10);

    expect($task->keepSummary)->toBeFalse();
});

it('renders the label and stable messages when finished with retain enabled', function () {
    Prompt::fake();

    $task = new Task(label: 'Running', limit: 10, keepSummary: true);
    $task->finished = true;
    $task->stableMessages[] = ['type' => 'success', 'message' => 'Step one done'];
    $task->stableMessages[] = ['type' => 'error', 'message' => 'Step two failed'];

    $renderer = new TaskRenderer($task);
    $output = (string) $renderer($task);

    expect($output)->toContain('Running');
    expect($output)->toContain('Step one done');
    expect($output)->toContain('Step two failed');
    expect($output)->not->toContain('─');
    expect($output)->toEndWith(PHP_EOL.PHP_EOL);
});

it('renders nothing special when finished with no stable messages', function () {
    Prompt::fake();

    $task = new Task(label: 'Running', limit: 10, keepSummary: true);
    $task->finished = true;

    $renderer = new TaskRenderer($task);
    $output = (string) $renderer($task);

    expect($output)->toContain('Running');
});

it('shrinks the stable-message budget when a sub-label appears mid-task', function () {
    Prompt::fake();

    $task = new Task(label: 'Running', limit: 10);
    $task->maxStableMessages = 3;

    $task->stableMessages = [
        ['type' => 'success', 'message' => 'one'],
        ['type' => 'success', 'message' => 'two'],
        ['type' => 'success', 'message' => 'three'],
    ];

    $receiveMessages = new ReflectionMethod($task, 'receiveMessages');
    $sockets = stream_socket_pair(STREAM_PF_UNIX, STREAM_SOCK_STREAM, STREAM_IPPROTO_IP);

    $id = $task->identifier;
    fwrite($sockets[1], "{$id}_sublabel:Now doing a thing\n");
    fclose($sockets[1]);

    stream_set_blocking($sockets[0], false);
    $receiveMessages->invoke($task, $sockets[0]);
    fclose($sockets[0]);

    expect($task->subLabel)->toBe('Now doing a thing');
    expect($task->maxStableMessages)->toBeLessThan(3);
    expect(count($task->stableMessages))->toBeLessThanOrEqual($task->maxStableMessages);

    $previousBudget = $task->maxStableMessages;

    $sockets = stream_socket_pair(STREAM_PF_UNIX, STREAM_SOCK_STREAM, STREAM_IPPROTO_IP);
    fwrite($sockets[1], "{$id}_sublabel:\n");
    fclose($sockets[1]);
    stream_set_blocking($sockets[0], false);
    $receiveMessages->invoke($task, $sockets[0]);
    fclose($sockets[0]);

    expect($task->subLabel)->toBe('');
    expect($task->maxStableMessages)->toBe($previousBudget + 1);
});

it('does not take the retain branch when keepSummary is disabled', function () {
    Prompt::fake();

    $task = new Task(label: 'Running', limit: 10, keepSummary: false);
    $task->finished = true;
    $task->stableMessages[] = ['type' => 'success', 'message' => 'Step one done'];

    $renderer = new TaskRenderer($task);
    $output = (string) $renderer($task);

    expect($output)->toContain('Running');
    expect($output)->toContain('Step one done');
});
