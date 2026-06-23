<?php

namespace Laravel\Prompts\Elements;

class NumberedList implements ElementContract
{
    public function __construct(protected array $items)
    {
        //
    }

    public function content(): array
    {
        return $this->items;
    }
}
