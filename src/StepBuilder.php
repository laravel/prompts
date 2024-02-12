<?php

namespace Laravel\Prompts;

use Closure;

class StepBuilder
{
    public function __construct(
        public array $steps = [],
        public string $title = 'Step',
    ) {
    }

    public function add(Closure $step, Closure|false|null $revert = null): static
    {
        if ($revert === null) {
            $revert = fn () => null;
        }

        $this->steps[] = new Step($step, $revert);

        return $this;
    }

    public function display(): array
    {
        return (new StepPrompt($this->steps, $this->title))->prompt();
    }
}
