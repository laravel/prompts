<?php

namespace Laravel\Prompts;

class SpinnerMessenger
{
    public function __construct(protected Connection $outputSocket, protected Connection $messageSocket)
    {
        //
    }

    /**
     * Write a message to the output socket.
     */
    public function output(string $message): void
    {
        $this->outputSocket->write($message);
    }

    /**
     * Write a message to the output socket with a new line.
     */
    public function line(string $message): void
    {
        $this->output($message.PHP_EOL);
    }

    /**
     * Write a message to the message socket.
     */
    public function message(string $message): void
    {
        $this->messageSocket->write($message);
    }

    /**
     * Write the stop indicator to the output socket.
     */
    public function stop(string $stopIndicator)
    {
        $this->line($stopIndicator);
    }
}
