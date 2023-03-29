<?php

namespace Laravel\Prompts\Concerns;

trait Cursor
{
    /**
     * Hide the cursor.
     */
    public function hideCursor(): void
    {
        $this->terminal()->write("\e[?25l");
    }

    /**
     * Show the cursor.
     */
    public function showCursor(): void
    {
        $this->terminal()->write("\e[?25h");
    }

    /**
     * Move the cursor.
     */
    public function moveCursor(int $x, int $y = 0): void
    {
        $sequence = '';

        if ($x < 0) {
            $sequence .= "\e[".abs($x).'D'; // Left
        } elseif ($x > 0) {
            $sequence .= "\e[{$x}C"; // Right
        }

        if ($y < 0) {
            $sequence .= "\e[".abs($y).'A'; // Up
        } elseif ($y > 0) {
            $sequence .= "\e[{$y}B"; // Down
        }

        $this->terminal()->write($sequence);
    }
}
