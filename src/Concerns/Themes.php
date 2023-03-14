<?php

namespace Laravel\Prompts\Concerns;

use Closure;
use Laravel\Prompts\Themes\ThemeFactory;

trait Themes
{
    protected Closure $renderer;

    protected static $theme = 'Laravel';

    public static function theme($theme = null)
    {
        if ($theme === null) {
            return static::$theme;
        }

        static::$theme = $theme;
    }

    public function withRenderer(Closure $renderer)
    {
        $this->renderer = $renderer;

        return $this;
    }

    protected function defaultRenderer()
    {
        return ThemeFactory::makeFor($this, static::theme());
    }

    protected function getRenderer()
    {
        return $this->renderer ?? $this->defaultRenderer();
    }

    protected function renderTheme()
    {
        $renderer = $this->getRenderer();

        return $renderer($this);
    }
}
