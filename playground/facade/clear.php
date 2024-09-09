<?php

use Laravel\Prompts\Support\Facades\Prompt;

require __DIR__ . '/../../vendor/autoload.php';

Prompt::note('This will disappear.');

Prompt::pause('Press [Enter] to continue.');

Prompt::clear();

Prompt::note('This will also disappear.');

Prompt::pause('Press [Enter] to continue.');

Prompt::clear();
