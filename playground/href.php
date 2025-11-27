<?php

use function Laravel\Prompts\href;

require __DIR__.'/../vendor/autoload.php';

href(
    message: 'Visit Laravel documentation:',
    path: 'https://laravel.com/docs',
    tooltip: 'Click here'
);

href(
    message: 'Open current file:',
    path: __FILE__,
    tooltip: 'Click here'
);

href(
    message: '<fg=green;options=bold>Visit Laravel Documentation:</>',
    path: 'https://laravel.com/docs',
    tooltip: 'Click here'
);

href(
    message: 'Visit Laravel Documentation:',
    path: 'https://laravel.com/docs'
);

href(
    message: '',
    path: 'https://laravel.com/docs'
);
