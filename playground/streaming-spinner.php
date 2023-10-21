<?php

use Laravel\Prompts\SpinnerMessenger;
use Symfony\Component\Process\Process;

use function Laravel\Prompts\spin;
use function Laravel\Prompts\text;

require __DIR__.'/../vendor/autoload.php';

spin(
    function (SpinnerMessenger $messenger) {
        $process = Process::fromShellCommandline('php '.__DIR__.'/streaming-spinner-process.php');
        $process->start();

        foreach ($process as $type => $data) {
            $messenger->output($data);
        }

        return 'Callback return';
    },
    'Updating Composer...',
);

foreach (range(1, 3) as $i) {
    if ($argv[1] ?? false) {
        text('Name '.$i, 'Default '.$i);
    }

    spin(
        function (SpinnerMessenger $messenger) {
            foreach (range(1, 50) as $i) {
                $messenger->line("<info>✔︎</info> <comment>Step {$i}</comment>");

                usleep(rand(50_000, 250_000));

                if ($i === 20) {
                    $messenger->message('Almost there...');
                }

                if ($i === 35) {
                    $messenger->message('Still going...');
                }
            }
        },
        'Taking necessary steps...',
    );
}
