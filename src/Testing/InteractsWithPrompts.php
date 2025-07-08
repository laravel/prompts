<?php

declare(strict_types=1);

namespace Laravel\Prompts\Testing;

use Illuminate\Support\Collection;
use Laravel\Prompts\Note;
use Laravel\Prompts\Table;
use Laravel\Prompts\Prompt;
use Illuminate\Testing\PendingCommand;
use Symfony\Component\Console\Output\BufferedOutput;

trait InteractsWithPrompts
{
    /**
     * This method is automatically called from the setUpTraits method of InteractsWithTestCaseLifecycle.
     */
    public function setUpInteractsWithPrompts(): void
    {
        $expectOutputFromPrompt = function (Prompt $prompt) {
            $prompt->setOutput($output = new BufferedOutput);

            $prompt->display();

            $this->expectsOutputToContain(trim($output->fetch()));

            return $this;
        };

        PendingCommand::macro(
            'expectsPromptError',
            fn (string $message) => $expectOutputFromPrompt->call(
                $this,
                new Note($message, 'error')
            )
        );

        PendingCommand::macro(
            'expectsPromptWarning',
            fn (string $message) => $expectOutputFromPrompt->call(
                $this,
                new Note($message, 'warning')
            )
        );

        PendingCommand::macro(
            'expectsPromptAlert',
            fn (string $message) => $expectOutputFromPrompt->call(
                $this,
                new Note($message, 'alert')
            )
        );

        PendingCommand::macro(
            'expectsPromptInfo',
            fn (string $message) => $expectOutputFromPrompt->call(
                $this,
                new Note($message, 'info')
            )
        );

        PendingCommand::macro(
            'expectsPromptIntro',
            fn (string $message) => $expectOutputFromPrompt->call(
                $this,
                new Note($message, 'intro')
            )
        );

        PendingCommand::macro(
            'expectsPromptOutro',
            fn (string $message) => $expectOutputFromPrompt->call(
                $this,
                new Note($message, 'outro')
            )
        );

        PendingCommand::macro(
            'expectsPromptTable',
            fn (array|Collection $headers, array|Collection|null $rows = null) => $expectOutputFromPrompt->call(
                $this,
                new Table($headers, $rows)
            )
        );
    }
}
