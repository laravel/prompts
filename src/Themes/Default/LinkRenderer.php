<?php

namespace Laravel\Prompts\Themes\Default;

use Laravel\Prompts\Concerns\Href;
use Laravel\Prompts\Link;

class LinkRenderer extends Renderer
{
    use Href;

    /**
     * Render the link.
     */
    public function __invoke(Link $link): string
    {
        $value = $this->href(
            $this->convertPathToUri($link->path),
            $link->tooltip
        );

        if ($link->message) {
            $this->line(" {$this->blue(" {$link->message} {$value}")}");
        } else {
            $this->line(" {$this->blue(" {$value}")}");
        }

        return $this;
    }

    protected function convertPathToUri(string $path): string
    {
        if (str_starts_with(strtolower($path), 'file://')) {
            return $path;
        }

        if (preg_match('/^[a-z]+:\/\//i', $path)) {
            return $path;
        }

        $path = '/'.ltrim(strtr($path, '\\', '/'), '/');

        return $this->isVSCode() ? "vscode://file{$path}" : "file://{$path}";
    }

    protected function isVSCode(): bool
    {
        return ($_SERVER['TERM_PROGRAM'] ?? null) === 'vscode';
    }
}
