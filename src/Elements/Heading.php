<?php

namespace Laravel\Prompts\Elements;

class Heading implements ElementContract
{
    public function __construct(protected string $text)
    {
        //
    }

    /**
     * @return array<int, string>
     */
    public function content(): array
    {
        return [$this->text];
    }
}
