<?php

use function Laravel\Prompts\textarea;

require __DIR__.'/../vendor/autoload.php';

$story = textarea(
    label: 'Tell me a story',
    placeholder: 'Weave me a tale',
    description: 'Write a creative story or narrative. You can use multiple paragraphs to organize your content.

Feel free to include dialogue, descriptions, and any creative elements you like. Use Ctrl+D when finished.'
);

var_dump($story);

echo str_repeat(PHP_EOL, 5);
