<?php

namespace Laravel\Prompts;

use Closure;
use Laravel\Prompts\Exceptions\StepRevertedException;

class StepFactory
{
    /**
     * The order of steps to execute.
     *
     * @var array<int, array{0: Closure(mixed): mixed, 1: (Closure(mixed): void)|false|null}>
     */
    protected array $steps = [];

    /**
     * Recorded responses from each step.
     *
     * @var array<int, mixed>
     */
    protected array $responses = [];

    /**
     * The current step.
     */
    protected int $currentStepIndex = 0;

    public function __construct(protected string $title)
    {
    }

    /**
     * Register the next step.
     *
     * @param Closure(mixed): mixed $prompt
     * @param (Closure(mixed): void)|false|null $revert
     */
    public function then(Closure $prompt, Closure|false $revert = null): static
    {
        $this->steps[] = [$prompt, $revert];

        return $this;
    }

    /**
     * Executes the steps in order.
     *
     * @return array<int, mixed>
     */
    public function run(): array
    {
        while ($this->currentStepIndex < count($this->steps)) {
            $previousResponse = $this->responses ? last($this->responses) : null;
            $step = $this->steps[$this->currentStepIndex];

            (new Step($this->title, $this->currentStepIndex + 1, count($this->steps)))->display();

            try {
                $response = call_user_func($step[0], $previousResponse);
                $this->responses[$this->currentStepIndex] = $response;
                $this->currentStepIndex++;
            } catch (StepRevertedException) {
                $this->revert();
            }
        }

        return $this->responses;
    }

    /**
     * Revert the last step.
     */
    protected function revert(): void
    {
        $previousStepIndex = max($this->currentStepIndex - 1, 0);
        $previousStep = $this->steps[$previousStepIndex];

        if ($previousStep[1] === false) {
            (new Note(sprintf('%s %d cannot be reverted.', $this->title, $previousStepIndex + 1), 'alert'))->display();
            return;
        }

        call_user_func(
            $previousStep[1] ?? fn () => null,
            $this->responses[$previousStepIndex] ?? null,
        );

        unset($this->responses[$previousStepIndex]);
        $this->currentStepIndex = $previousStepIndex;
    }
}
