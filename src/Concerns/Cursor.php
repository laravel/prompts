<?php

namespace Laravel\Prompts\Concerns;

trait Cursor
{
    const CSI = "\e[";

    const UP = 'A';

    const DOWN = 'B';

    const RIGHT = 'C';

    const LEFT = 'D';

    /**
     * Hide the cursor.
     *
     * @return void
     */
    public function hideCursor()
    {
        fwrite(STDOUT, static::CSI . "?25l");
    }

    /**
     * Show the cursor.
     *
     * @return void
     */
    public function showCursor()
    {
        fwrite(STDOUT, static::CSI . "?25h");
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
            $sequence .= static::CSI . abs($x) . static::LEFT;
        } else if ($x > 0) {
            $sequence .= static::CSI . $x . static::RIGHT;
        }

        if ($y < 0) {
            $sequence .= static::CSI . abs($y) . static::UP;
        } else if ($y > 0) {
            $sequence .= static::CSI . $y . static::DOWN;
        }

        fwrite(STDOUT, $sequence);
    }
}
