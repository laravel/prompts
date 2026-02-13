<?php

namespace Laravel\Prompts\Themes\Default;

use Laravel\Prompts\Concerns\Href as HrefConcern;
use Laravel\Prompts\Href;

class HrefRenderer extends Renderer
{
    use HrefConcern;

    /**
     * Render the href.
     */
    public function __invoke(Href $href): string
    {
        $value = $this->href(
            $this->convertPathToUri($href->path),
            $href->tooltip
        );

        if ($href->message) {
            $this->line(" {$this->blue(" {$href->message} {$value}")}");
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
