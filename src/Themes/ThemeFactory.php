<?php

namespace Laravel\Prompts\Themes;

use Closure;
use ReflectionClass;

class ThemeFactory
{
    public static function makeFor($prompt, $theme)
    {
        $class = (new ReflectionClass(self::class))->getNamespaceName()
            . "\\$theme\\"
            . (new ReflectionClass($prompt))->getShortName()
            . 'Renderer';

        if (! class_exists($class)) {
            throw new \Exception('Renderer not found [' . $class . ']');
        }

        return Closure::fromCallable(new $class);
    }
}
