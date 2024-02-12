<?php

namespace Laravel\Prompts;

use Closure;

class Step
{
    protected mixed $response = null;

    public function __construct(
        protected Closure $action,
        public readonly Closure|false $revert,
    ) {
    }

    public function run(StepPrompt $prompt): void
    {
        $this->response = call_user_func($this->action, $prompt->value());
    }

    public function getResponse(): mixed
    {
        return $this->response;
    }
}
