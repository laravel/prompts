<?php

namespace Laravel\Prompts\Elements;

class BulletedList implements Contract
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
