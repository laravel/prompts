<?php

namespace Laravel\Prompts\Elements;

class NumberedList implements ElementContract
{
    /**
     * @param  array<int, string>  $items
     */
    public function __construct(protected array $items)
    {
        //
    }

    /**
     * @return array<int, string>
     */
    public function content(): array
    {
        return $this->items;
    }
}
