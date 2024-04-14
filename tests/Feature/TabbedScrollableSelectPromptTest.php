<?php

use Illuminate\Support\Collection;
use Laravel\Prompts\Key;
use Laravel\Prompts\Prompt;

use function Laravel\Prompts\tabbedscrollableselect;

beforeEach(function () {
    $this->optionsData = [
        [
            'id' => 'ae912f',
            'tab' => 'Mine',
            'body' => 'This is my essay.',
        ],
        [
            'id' => 'b3c4d5',
            'tab' => 'Yours',
            'body' => 'This is your essay.',
        ],
        [
            'id' => 'f6g7h8',
            'tab' => 'Theirs',
            'body' => 'This is their essay.',
        ],
    ];
});


it('accepts an array of options', function () {
    Prompt::fake([Key::RIGHT, Key::RIGHT, Key::ENTER]);

    $result = tabbedscrollableselect(
        label: 'Whose essay is best?',
        options: $this->optionsData,
    );

    expect($result)->toBe('f6g7h8');

    Prompt::assertStrippedOutputContains($this->optionsData[0]['tab']);
    Prompt::assertStrippedOutputContains($this->optionsData[0]['body']);
    Prompt::assertStrippedOutputContains($this->optionsData[1]['tab']);
    Prompt::assertStrippedOutputContains($this->optionsData[1]['body']);
    Prompt::assertStrippedOutputContains($this->optionsData[2]['tab']);
    Prompt::assertStrippedOutputContains($this->optionsData[2]['body']);
});

it('accepts a collection of options', function () {
    Prompt::fake([Key::RIGHT, Key::RIGHT, Key::ENTER]);

    $result = tabbedscrollableselect(
        label: 'Whose essay is best?',
        options: collect($this->optionsData),
    );

    expect($result)->toBe('f6g7h8');

    Prompt::assertStrippedOutputContains($this->optionsData[0]['tab']);
    Prompt::assertStrippedOutputContains($this->optionsData[0]['body']);
    Prompt::assertStrippedOutputContains($this->optionsData[1]['tab']);
    Prompt::assertStrippedOutputContains($this->optionsData[1]['body']);
    Prompt::assertStrippedOutputContains($this->optionsData[2]['tab']);
    Prompt::assertStrippedOutputContains($this->optionsData[2]['body']);
});

it('accepts a default value', function () {
    Prompt::fake([Key::ENTER]);

    $result = tabbedscrollableselect(
        label: 'Whose essay is best?',
        options: $this->optionsData,
        default: 1,
    );

    expect($result)->toBe('b3c4d5');

    Prompt::assertStrippedOutputDoesntContain($this->optionsData[0]['body']);
    Prompt::assertStrippedOutputContains($this->optionsData[1]['tab']);
    Prompt::assertStrippedOutputContains($this->optionsData[1]['body']);
    Prompt::assertStrippedOutputContains($this->optionsData[2]['tab']);
    Prompt::assertStrippedOutputDoesntContain($this->optionsData[2]['body']);
});

it('accepts a closure as a default value', function () {
    Prompt::fake([Key::ENTER]);

    $result = tabbedscrollableselect(
        label: 'Whose essay is best?',
        options: collect($this->optionsData),
        default: fn (Collection $options): Collection => $options->where('tab', 'Yours'),
    );

    expect($result)->toBe('b3c4d5');

    Prompt::assertStrippedOutputDoesntContain($this->optionsData[0]['body']);
    Prompt::assertStrippedOutputContains($this->optionsData[1]['tab']);
    Prompt::assertStrippedOutputContains($this->optionsData[1]['body']);
    Prompt::assertStrippedOutputContains($this->optionsData[2]['tab']);
    Prompt::assertStrippedOutputDoesntContain($this->optionsData[2]['body']);
});

it('accepts a scroll value and enforces the minimum', function () {
    Prompt::fake([Key::ENTER]);

    $result = tabbedscrollableselect(
        label: 'This content should only display 5 lines of text.',
        options: [
            [
                'id' => 0,
                'tab' => 'Mine',
                'body' => collect([
                    'line 1',
                    'line 2',
                    'line 3',
                    'line 4',
                    'line 5',
                    'line 6',
                ])->implode(PHP_EOL),
            ],
        ],
        scroll: 2,
    );

    expect($result)->toBe(0);

    Prompt::assertStrippedOutputContains('line 1');
    Prompt::assertStrippedOutputContains('line 5');
    Prompt::assertStrippedOutputDoesntContain('line 6');
});

it('scrolls the content', function() {
    Prompt::fake([Key:: DOWN, Key::ENTER]);

    $result = tabbedscrollableselect(
        label: 'This content should display the 6th line.',
        options: [
            [
                'id' => 0,
                'tab' => 'Mine',
                'body' => collect([
                    'line 1',
                    'line 2',
                    'line 3',
                    'line 4',
                    'line 5',
                    'line 6',
                ])->implode(PHP_EOL),
            ],
        ],
        scroll: 2,
    );

    expect($result)->toBe(0);

    Prompt::assertStrippedOutputContains('line 6');
});

it('accepts a validate value')->todo();

it('accepts a hint value', function () {
    Prompt::fake([Key::ENTER]);

    $result = tabbedscrollableselect(
        label: 'Whose essay is best?',
        options: $this->optionsData,
        hint: 'Use the arrow keys to navigate.',
    );

    expect($result)->toBe('ae912f');

    Prompt::assertStrippedOutputContains('Use the arrow keys to navigate.');

});

it('skips [ESC] instruction if required is true', function () {
    Prompt::fake([Key::ESCAPE, KEY::ENTER]);

    $result = tabbedscrollableselect(
        label: 'Whose essay is best?',
        options: $this->optionsData,
        required: true,
    );

    expect($result)->toBe('ae912f');

    Prompt::assertStrippedOutputDoesntContain('[ESCAPE]');
    Prompt::assertStrippedOutputContains('You must select an option.');
});

it('allows to escape if required is false', function () {
    Prompt::fake([Key::ESCAPE]);

    $result = tabbedscrollableselect(
        label: 'Whose essay is best?',
        options: $this->optionsData,
        required: false,
    );

    expect($result)->toBeNull();

    Prompt::assertStrippedOutputContains('[ESCAPE]');
    Prompt::assertStrippedOutputContains('No Option Selected');
});
