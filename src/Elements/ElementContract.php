<?php

namespace Laravel\Prompts\Elements;

interface ElementContract
{
    /**
     * @return array<array-key, string>
     */
    public function content(): array;
}
