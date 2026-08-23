<?php

use Laravel\Prompts\Terminal;

it('does not throw when restoring the tty fails', function () {
    $terminal = new class extends Terminal
    {
        public function setInitialTtyMode(string $mode): void
        {
            $this->initialTtyMode = $mode;
        }

        protected function exec(string $command): string
        {
            throw new RuntimeException("stty: invalid argument '{$command}'");
        }
    };

    $terminal->setInitialTtyMode('6902:3:4b00:5cb:4:ff:ff:7f:17:15:12:ff:3:1c:1a:19:11:13:16:f:1:0:14:ff');

    $terminal->restoreTty();
})->throwsNoExceptions();
