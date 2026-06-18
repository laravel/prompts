<?php

namespace Laravel\Prompts\Elements;

class Heading implements Contract
{
    public function __construct(protected string $text)
    {
        //
    }

    public function content(): array
    {
        return [$this->text];
    }
}
