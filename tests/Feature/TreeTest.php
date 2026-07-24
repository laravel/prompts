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
