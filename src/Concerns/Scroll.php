<?php

namespace Laravel\Prompts\Concerns;

trait Scroll
{
    /**
     * The number of items to show before scrolling.
     */
    public function scroll(): int|false
    {
        if (! isset($this->scroll) || $this->scroll === true) {
            return 5;
        }

        return $this->scroll;
    }

    /**
     * Get a scrolled version of the items.
     *
     * @param  array<int, mixed>  $items
     * @return array<int, mixed>
     */
    public function scrolled(array $items, int|null $highlighted): array
    {
        $scroll = $this->scroll();

        if ($scroll === false) {
            return $items;
        }

        $count = count($items);

        if ($count <= $scroll) {
            return $items;
        }

        if ($highlighted === null) {
            return array_slice($items, 0, $scroll, true);
        }

        if ($highlighted < $scroll) {
            return array_slice($items, 0, $scroll, true);
        }

        return array_slice($items, $highlighted - $scroll + 1, $scroll, true);
    }

    /**
     * Return whether there are items above the current scroll position.
     *
     * @param  array<int, mixed>  $items
     */
    public function hasItemsAbove(array $items, int|null $highlighted): bool
    {
        $scroll = $this->scroll();

        if ($scroll === false) {
            return false;
        }

        return $highlighted !== null && $highlighted > $scroll - 1;
    }

    /**
     * Return whether there are items below the current scroll position.
     *
     * @param  array<int, mixed>  $items
     */
    public function hasItemsBelow(array $items, int|null $highlighted): bool
    {
        $scroll = $this->scroll();

        if ($scroll === false) {
            return false;
        }

        $count = count($items);

        if ($count <= $scroll) {
            return false;
        }

        return $highlighted < $count - 1;
    }
}
