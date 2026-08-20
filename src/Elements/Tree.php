<?php

namespace Laravel\Prompts\Elements;

class Tree implements ElementContract
{
    /**
     * @param  array<int|string, string|array<int|string, mixed>>  $items
     */
    public function __construct(public readonly array $items)
    {
        //
    }
}
