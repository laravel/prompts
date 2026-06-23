<?php

namespace Laravel\Prompts\Elements;

class Heading implements ElementContract
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
