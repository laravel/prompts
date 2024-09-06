<?php

namespace Laravel\Prompts\Themes\Default;

use Laravel\Prompts\Clear;

class ClearRenderer extends Renderer
{
    /**
     * Clear the console screen.
     */
    public function __invoke(Clear $clear)
    {
        return $clear->value();
    }
}
