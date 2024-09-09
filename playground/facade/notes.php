<?php

use Laravel\Prompts\Support\Facades\Prompt;

require __DIR__ . '/../../vendor/autoload.php';

Prompt::intro('Intro');
Prompt::note('Note');
Prompt::info('Info');
Prompt::warning('Warning');
Prompt::error('Error');
Prompt::alert('Alert');
Prompt::outro('Outro');
