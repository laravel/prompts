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
        $output = '';

        foreach ($this->outputToTask->read() as $chunk) {
            $output .= $chunk;
        }

        return $output;
    }

    /**
     * Get the most recent message from the spinner.
     */
    public function message(): string
    {
        $message = '';

        foreach ($this->messageToTask->read() as $chunk) {
            $message .= $chunk;
        }

        return $message;
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
