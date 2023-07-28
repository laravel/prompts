<?php

use Laravel\Prompts\P;

require __DIR__.'/../vendor/autoload.php';

P::intro('Intro');
P::note('Note');
P::warning('Warning');
P::error('Error');
P::alert('Alert');
P::outro('Outro');
