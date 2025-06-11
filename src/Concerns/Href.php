<?php

namespace Laravel\Prompts\Concerns;

trait Href
{
    public function href(string $path, ?string $tooltip = null): string
    {
        $tooltip = $tooltip ?: $path;

        return "\033]8;;{$path}\033\\{$tooltip}\033]8;;\033\\";
    }
}
