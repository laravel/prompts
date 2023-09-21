<?php

namespace Laravel\Prompts;

class SpinnerMessenger
{
    public function __construct(protected Connection $socket)
    {
    }

    public function send(string $message): void
    {
        $this->socket->write($message);
    }
}
