<?php

namespace Laravel\Prompts\Elements;

class BulletedList implements ElementContract
{
    /**
     * @param  array<int, string>  $items
     */
    public function __construct(
        protected array $items,
        public readonly bool $spaced = false,
    ) {
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
