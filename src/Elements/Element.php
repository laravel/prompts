<?php

namespace Laravel\Prompts\Elements;

class Element
{
    public static function heading(string $text): Heading
    {
        return new Heading($text);
    }

    public static function bulletedList(array $items): BulletedList
    {
        return new BulletedList($items);
    }

    public static function numberedList(array $items): NumberedList
    {
        return new NumberedList($items);
    }
}
