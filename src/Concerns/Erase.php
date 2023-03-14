<?php

namespace Laravel\Prompts\Concerns;

trait Erase
{
    /**
     * Erase the given number of lines downwards from the cursor position.
     *
     * @param  int  $count
     * @return void
     */
    public function eraseLines($count)
    {
        $clear = '';
        for ($i = 0; $i < $count; $i++) {
            $clear .= "\e[2K" . ($i < $count - 1 ? "\e[{$count}A" : '');
        }

        if ($count) {
            $clear .= "\e[G";
        }

        fwrite(STDOUT, $clear);
    }

    /**
     * Erase from cursor until end of screen.
     *
     * @return void
     */
    public function eraseDown()
    {
        fwrite(STDOUT, "\e[J");
    }
}
