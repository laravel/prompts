<?php

namespace Laravel\Prompts\Elements;

class Link implements ElementContract
{
    public function __construct(
        public readonly string $url,
        public readonly ?string $label = null,
        public readonly bool $underline = true,
    ) {
        $this->label ??= $this->url;
    }

    public function __toString(): string
    {
        $text = ($this->underline) ? "\e[4m{$this->label}\e[24m" : $this->label;

        return "\e]8;;{$this->url}\e\\{$text}\e]8;;\e\\";
    }
}
