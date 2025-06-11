<?php

use function Laravel\Prompts\link;

require __DIR__.'/../vendor/autoload.php';

link(
    message: 'Visit Laravel documentation:',
    path: 'https://laravel.com/docs',
    tooltip: 'Click here'
);

link(
    message: 'Open current file:',
    path: __FILE__,
    tooltip: 'Click here'
);

link(
    message: '<fg=green;options=bold>Visit Laravel Documentation:</>',
    path:  'https://laravel.com/docs',
    tooltip: 'Click here'
);

link(
    message: '',
    path: 'https://laravel.com/docs'
);

link(
    message: 'Visit Laravel Documentation:',
    path: 'https://laravel.com/docs'
);
