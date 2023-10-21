<?php

namespace Laravel\Prompts;

class SpinnerMessenger
{
    public function __construct(protected Connection $outputSocket, protected Connection $messageSocket)
    {
    }

    public function output(string $message): void
    {
        $this->outputSocket->write($message);
    }

    public function line(string $message): void
    {
        $this->output($message.PHP_EOL);
    }

    public function message(string $message): void
    {
        $this->messageSocket->write($message);
    }

    public function stop(string $stopIndicator)
    {
        $this->line($stopIndicator);
    }
}
