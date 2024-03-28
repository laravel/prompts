<?php

namespace Laravel\Prompts;

class SpinnerSockets
{
    public function __construct(
        protected Connection $outputToSpinner,
        protected Connection $outputToTask,
        protected Connection $messageToSpinner,
        protected Connection $messageToTask,
    ) {
        //
    }

    /**
     * Create a new instance with pairs of sockets.
     */
    public static function create(): self
    {
        [$outputSocketToSpinner, $outputSocketToTask] = Connection::createPair();
        [$messageSocketToSpinner, $messageSocketToTask] = Connection::createPair();

        return new self(
            $outputSocketToSpinner,
            $outputSocketToTask,
            $messageSocketToSpinner,
            $messageSocketToTask,
        );
    }

    /**
     * Get a new messenger instance for the spinner.
     */
    public function messenger(): SpinnerMessenger
    {
        return new SpinnerMessenger($this->outputToSpinner, $this->messageToSpinner);
    }

    /**
     * Get the streaming output from the spinner.
     */
    public function streamingOutput(): string
    {
        return $this->getSocketOutput($this->outputToTask);
    }

    /**
     * Get the most recent message from the spinner.
     */
    public function message(): string
    {
        return $this->getSocketOutput($this->messageToTask);
    }

    /**
     * Send the previous frame back to the parent process.
     */
    public function sendPrevFrame(string $prevFrame)
    {
        $this->outputToTask->write($prevFrame);
    }

    /**
     * Read the previous frame from the spinner.
     */
    public function prevFrame(): string
    {
        return $this->getSocketOutput($this->outputToSpinner);
    }

    /**
     * Read the output from the given socket.
     */
    protected function getSocketOutput(Connection $socket)
    {
        $output = '';

        foreach ($socket->read() as $chunk) {
            $output .= $chunk;
        }

        return $output;
    }

    /**
     * Close the sockets.
     */
    public function close(): void
    {
        $this->outputToSpinner->close();
        $this->outputToTask->close();
        $this->messageToSpinner->close();
        $this->messageToTask->close();
    }
}
