<?php

namespace Laravel\Prompts;

class ViewState
{
    /**
     * view items list
     *
     * @var array<int, int>
     */
    public array $items = [];

    /**
     * Create a list scroll view state instance.
     */
    public function __construct(public int $height, public int $count)
    {
        $this->resetStart();
    }

    /**
     * Reset scroll view to list top.
     */
    public function resetStart(): void
    {
        $this->items = range(0, min($this->height - 1, $this->count - 1));
    }

    /**
     * Reset scroll view to list bottom.
     */
    public function resetEnd(): void
    {
        $this->items = range(max($this->count - $this->height, 0), $this->count - 1);
    }

    /**
     * Reset the list item count.
     */
    public function resetCount(int $count): void
    {
        $this->count = $count;
    }

    /**
     * Get the first view item key.
     */
    public function first(): int
    {
        return $this->items[0];
    }

    /**
     * Get the last view item key.
     */
    public function last(): int
    {
        return $this->items[count($this->items) - 1];
    }

    /**
     * Update view by passing first view key.
     */
    public function update(int $first): void
    {
        $this->items = range($first, min($first + $this->height - 1, $this->count - 1));
    }

    /**
     * Scroll up one item.
     */
    public function scrollUp(): void
    {
        if ($this->first() === 0) {
            // scroll to the bottom
            $this->items = range(max($this->count - $this->height, 0), $this->count - 1);
        } else {
            $this->items = range($this->first() - 1, min($this->first() - 1 + $this->height, $this->count - 1));
        }
    }

    /**
     * Scroll down one item.
     */
    public function scrollDown(): void
    {
        if ($this->last() === $this->count - 1) {
            $this->items = range(0, min($this->height - 1, $this->count - 1));
        } else {
            $this->items = range($this->first() + 1, min($this->first() + $this->height, $this->count - 1));
        }
    }
}
