<?php

namespace Laravel\Prompts\Concerns;

trait Cursor
{
    /**
     * Hide the cursor.
     *
     * @return void
     */
    public function hideCursor()
    {
        fwrite(STDOUT, "\e[?25l");
    }

    /**
     * Show the cursor.
     *
     * @return void
     */
    public function showCursor()
    {
        fwrite(STDOUT, "\e[?25h");
    }

    /**
     * Move the cursor.
     *
     * @param  int  $x
     * @param  int  $y
     * @return void
     */
    public function moveCursor($x, $y = 0)
    {
        $sequence = '';

        if ($x < 0) {
            $sequence .= "\e[".abs($x).'D'; // Left
        } else if ($x > 0) {
            $sequence .= "\e[{$x}C"; // Right
        }

        if ($y < 0) {
            $sequence .= "\e[".abs($y).'A'; // Up
        } else if ($y > 0) {
            $sequence .= "\e[{$y}B"; // Down
        }

        fwrite(STDOUT, $sequence);
    }
}
