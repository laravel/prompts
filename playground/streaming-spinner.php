<?php

use Laravel\Prompts\SpinnerMessenger;
use Symfony\Component\Process\Process;

use function Laravel\Prompts\spin;
use function Laravel\Prompts\text;

require __DIR__ . '/../vendor/autoload.php';

spin(
    function (SpinnerMessenger $messenger) {
        $process = Process::fromShellCommandline('composer update');
        $process->start();

        foreach ($process as $type => $data) {
            if ($process::ERR === $type) {
                $messenger->output($data);
            }
        }

        return 'Callback return';
    },
    'Updating Composer...',
);

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
