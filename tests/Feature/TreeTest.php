<?php

use Laravel\Prompts\Prompt;
use Laravel\Prompts\Tree;

use function Laravel\Prompts\tree;

it('renders a tree', function () {
    Prompt::fake();

    tree([
        'src' => [
            'Elements' => ['Element.php', 'Tree.php'],
            'helpers.php',
        ],
        'README.md',
    ]);

    Prompt::assertStrippedOutputContains('├─ src');
    Prompt::assertStrippedOutputContains('│  ├─ Elements');
    Prompt::assertStrippedOutputContains('│  │  ├─ Element.php');
    Prompt::assertStrippedOutputContains('│  │  └─ Tree.php');
    Prompt::assertStrippedOutputContains('│  └─ helpers.php');
    Prompt::assertStrippedOutputContains('└─ README.md');
});

it('auto-formats tree labels', function () {
    Prompt::fake();

    tree([
        'Run `composer install`',
    ]);

    Prompt::assertStrippedOutputContains('└─ Run `composer install`');
    Prompt::assertOutputContains("\e[36m`composer install`");
});

it('wraps long labels at the terminal width and keeps the guides aligned', function () {
    Prompt::fake();

    tree([
        'vendor' => [
            'This is a very long dependency description that will definitely exceed the width of the fake terminal and wrap onto a second line',
        ],
        'composer.json',
    ]);

    $content = Prompt::strippedContent();

    expect($content)->toContain('├─ vendor');
    expect($content)->toContain('│  └─ This is a very long dependency description');
    expect($content)->toContain('└─ composer.json');

    // The wrapped continuation keeps the vendor guide: "│" followed by five spaces.
    expect($content)->toMatch('/│ {5}\S/u');
});

it('renders nothing for an empty tree', function () {
    Prompt::fake();

    tree([]);

    expect(trim(Prompt::strippedContent()))->toBe('');
});

it('can fall back', function () {
    Prompt::fallbackWhen(true);

    Tree::fallbackUsing(function (Tree $tree) {
        expect($tree->items)->toBe(['README.md']);

        return true;
    });

    $result = (new Tree(['README.md']))->display();

    expect($result)->toBeNull();
});
