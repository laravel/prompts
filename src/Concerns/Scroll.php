<?php

namespace Laravel\Prompts\Concerns;

trait Scroll
{
    /**
     * The number of items to show before scrolling.
     */
    public int $scroll = 5;

    /**
     * Get a scrolled version of the items.
     *
     * @param  array<int, mixed>  $items
     * @return array<int, mixed>
     */
    public function scrolled(array $items, int|null $highlighted): array
    {
        $count = count($items);

        if ($count <= $this->scroll) {
            return $items;
        }

        if ($highlighted === null) {
            return array_slice($items, 0, $this->scroll, true);
        }

        if ($highlighted < $this->scroll) {
            return array_slice($items, 0, $this->scroll, true);
        }

        return array_slice($items, $highlighted - $this->scroll + 1, $this->scroll, true);
    }

    /**
     * Return whether there are items above the current scroll position.
     *
     * @param  array<int, mixed>  $items
     */
    public function hasItemsAbove(array $items, int|null $highlighted): bool
    {
        return $highlighted !== null && $highlighted > $this->scroll - 1;
    }

    /**
     * Return whether there are items below the current scroll position.
     *
     * @param  array<int, mixed>  $items
     */
    public function hasItemsBelow(array $items, int|null $highlighted): bool
    {
        $count = count($items);

        if ($count <= $this->scroll) {
            return false;
        }

        return $highlighted < $count - 1;
    }
}
