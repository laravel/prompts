<?php

namespace Laravel\Prompts;

use Laravel\Prompts\Exceptions\StepRevertedException;
use Laravel\Prompts\Output\GroupConsoleOutput;

class StepPrompt extends Prompt
{
    protected int $currentStep = 0;

    protected array $responses = [];

    public function __construct(
        protected array $steps,
        public string   $title,
    )
    {
    }

    public function value(): array
    {
        return $this->responses;
    }

    public function prompt(): mixed
    {
        $this->capturePreviousNewLines();

        $this->state = 'initial';
        $this->render();

        while ($this->currentStep < count($this->steps)) {
            $this->state = 'active';
            $this->render();

            try {
                $this->step()->run($this);
                $this->responses[$this->currentStep] = $this->step()->getResponse();
                $this->currentStep++;
            } catch (StepRevertedException $e) {
                $this->revertStep();
            }
        }

        $this->state = 'complete';
        $this->render();

        return $this->value();
    }

    public function step(): Step
    {
        return $this->steps[$this->currentStep];
    }

    protected function revertStep(): void
    {
        $this->currentStep = max($this->currentStep - 1, 0);

        if (!$this->step()->revert) {
            $this->state = 'error';
            $this->render();
            $this->currentStep++;

            return;
        }

        $this->state = 'reverting';
        $this->render();
        call_user_func($this->step()->revert, $this->currentResponse());
    }

    protected function currentResponse(): mixed
    {
        return $this->responses[$this->currentStep] ?? null;
    }

    public function currentStepNumber(): int
    {
        return $this->currentStep + 1;
    }

    public function totalSteps(): int
    {
        return count($this->steps);
    }

    protected function render(): void
    {
        static::$output->write($this->renderTheme());
    }
}
