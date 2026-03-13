<?php

namespace Laravel\Prompts;

use Laravel\Prompts\Themes\Default\Concerns\InteractsWithStrings;

class Stream extends Prompt
{
    use InteractsWithStrings;

    public string $message = '';

    public array $currentlyFading = [];

    public int $maxWidth = 0;

    public array $fadingOutColors = [];

    /**
     * Create a new Stream instance.
     */
    public function __construct()
    {
        $this->maxWidth = static::terminal()->cols() - 20;
        $this->hideCursor();
        $this->fadingOutColors = $this->fadeOut();
    }

    public function append(string $message): void
    {
        $this->currentlyFading[] = $message;

        while (count($this->currentlyFading) > count($this->fadingOutColors)) {
            $this->message .= array_shift($this->currentlyFading);
        }

        $this->render();
    }

    public function close(): void
    {
        try {
            while (count($this->currentlyFading) > 0) {
                $this->message .= array_shift($this->currentlyFading);
                $this->render();
                usleep(25_000);
            }
        } finally {
            $this->showCursor();
        }
    }

    public function lines()
    {
        $toFadeIn = [];

        foreach ($this->currentlyFading as $index => $message) {
            $toFadeIn[] = $this->fadingOutColors[$index]($message);
        }

        $lines = explode(PHP_EOL, $this->message . implode('', $toFadeIn));
        $finalLines = [];

        foreach ($lines as $line) {
            $wrapped = $this->wordwrapWithAnsi($line, $this->maxWidth);
            $finalLines = array_merge($finalLines,  $wrapped);
        }

        return $finalLines;
    }

    public function prompt(): mixed
    {
        throw new \RuntimeException('Stream cannot be prompted');
    }

    /**
     * Get the value of the prompt.
     */
    public function value(): string
    {
        return $this->message . implode('', $this->currentlyFading);
    }
}
