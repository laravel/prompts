<?php

namespace Laravel\Prompts\Elements;

class Link implements ElementContract
{
    public function __construct(
        protected string $url,
        protected ?string $label = null,
        public readonly bool $underline = true,
    ) {
        //
    }

    /**
     * @return array<string, string>
     */
    public function content(): array
    {
        return [$this->url, $this->label ?? $this->url];
    }

    public function __toString(): string
    {
        $text = $this->label ?? $this->url;

        if ($this->underline) {
            $text = "\e[4m{$text}\e[24m";
        }

        return "\e]8;;{$this->url}\e\\{$text}\e]8;;\e\\";
    }
}
