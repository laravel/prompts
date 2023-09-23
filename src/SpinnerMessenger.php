<?php

namespace Laravel\Prompts;

class SpinnerMessenger
{
    public function __construct(protected Connection $socket, protected Connection $labelSocket)
    {
    }

    public function output(string $message): void
    {
        $this->socket->write($message);
    }

    public function line(string $message): void
    {
        $this->output($message.PHP_EOL);
    }

    public function message(string $message): void
    {
        $this->labelSocket->write($message);
    }
}
