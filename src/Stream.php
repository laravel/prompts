<?php

namespace Laravel\Prompts;

class Stream extends Prompt
{
    use Concerns\Truncation;

    public string $message = '';

    public array $fadingIn = [];

    public int $maxWidth = 0;

    /**
     * Create a new Stream instance.
     */
    public function __construct()
    {
        $this->maxWidth = static::terminal()->cols() - 20;
        $this->hideCursor();
    }

    public function append(string $message): void
    {
        $this->fadingIn[] = $message;

        if (count($this->fadingIn) > 3) {
            $this->message .= array_shift($this->fadingIn);
        }

        $this->render();
    }

    public function close(): void
    {
        while (count($this->fadingIn) > 0) {
            $this->message .= array_shift($this->fadingIn);
            $this->render();
            usleep(25_000);
        }
    }

    public function lines()
    {
        $toFadeIn = implode('', $this->fadingIn);
        $lines = explode(PHP_EOL, $this->message . $this->dim($toFadeIn));
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
        return $this->message;
    }
}
