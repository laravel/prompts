<?php

namespace Laravel\Prompts\Exceptions;

use Laravel\Prompts\Prompt;
use RuntimeException;

class StepRevertedException extends RuntimeException
{
    public Prompt $prompt;

    public function __construct(Prompt $prompt, string $message = "", int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->prompt = $prompt;
    }
}
