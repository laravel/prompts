<?php

namespace Laravel\Prompts\Elements;

class KeyValueList implements ElementContract
{
    /**
     * @param  array<string, string>  $items
     */
    public function __construct(protected array $items)
    {
        //
    }

    /**
     * @return array<string, string>
     */
    public function content(): array
    {
        return $this->items;
    }
}
