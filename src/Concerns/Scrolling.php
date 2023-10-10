<?php

namespace Laravel\Prompts\Concerns;

use Laravel\Prompts\Themes\Contracts\Scrolling as ScrollingRenderer;

trait Scrolling
{
    /**
     * The number of items to display before scrolling.
     */
    public int $scroll;

    /**
     * The index of the highlighted option.
     */
    public ?int $highlighted;

    /**
     * The index of the first visible option.
     */
    public int $firstVisible = 0;

    /**
     * Initialize scrolling.
     */
    protected function initializeScrolling(int $highlighted = null): void
    {
        $this->highlighted = $highlighted;

        $this->reduceScrollingToFitTerminal();
    }

    /**
     * Reduce the scroll property to fit the terminal height.
     */
    protected function reduceScrollingToFitTerminal(): void
    {
        $reservedLines = ($renderer = $this->getRenderer()) instanceof ScrollingRenderer ? $renderer->reservedLines() : 0;

        $this->scroll = min($this->scroll, $this->terminal()->lines() - $reservedLines);
    }

    /**
     * Highlight the given index.
     */
    protected function highlight(?int $index): void
    {
        $this->highlighted = $index;

        if ($this->highlighted === null) {
            return;
        }

        if ($this->highlighted < $this->firstVisible) {
            $this->firstVisible = $this->highlighted;
        } elseif ($this->highlighted > $this->firstVisible + $this->scroll - 1) {
            $this->firstVisible = $this->highlighted - $this->scroll + 1;
        }
    }

    /**
     * Offset the currently highlighted option.
     */
    protected function highlightOffset(int $offset, int $count, bool $allowNull = false): void
    {
        if ($offset === 0 || $count === 0) {
            return;
        }

        if ($allowNull) {
            if ($count === 1) {
                $this->highlight($this->highlighted === null ? 0 : null);
            } elseif ($this->highlighted === 0 && $offset < 0) {
                $this->highlight(null);
            } elseif ($this->highlighted === null) {
                $this->highlight($offset < 0 ? $count - 1 : 0);
            } elseif ($this->highlighted === $count - 1 && $offset > 0) {
                $this->highlight(null);
            } else {
                $this->highlight($offset < 0 ? max(0, $this->highlighted + $offset) : min($count - 1, $this->highlighted + $offset));
            }
        } else {
            if ($offset < 0) {
                $this->highlight($this->highlighted === 0 ? ($count - 1) : max(0, $this->highlighted + $offset));
            } else {
                $this->highlight($this->highlighted === $count - 1 ? 0 : min($count - 1, $this->highlighted + $offset));
            }
        }
    }

    /**
     * Center the highlighted option.
     */
    protected function scrollToHighlighted(int $count): void
    {
        if ($this->highlighted < $this->scroll) {
            return;
        }

        $remaining = $count - $this->highlighted - 1;
        $halfScroll = (int) floor($this->scroll / 2);
        $endOffset = max(0, $halfScroll - $remaining);

        if ($this->scroll % 2 === 0) {
            $endOffset--;
        }

        $this->firstVisible = $this->highlighted - $halfScroll - $endOffset;
    }
}
