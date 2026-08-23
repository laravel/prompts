<?php

namespace Laravel\Prompts\Concerns;

use InvalidArgumentException;

trait Truncation
{
    /**
     * Regular expression matching a single complete ANSI escape sequence:
     * CSI, OSC, or a two-character escape.
     */
    private const ESCAPE_SEQUENCE_PATTERN = '/\e\[[\x30-\x3f]*[\x20-\x2f]*[\x40-\x7e]|\e\][^\x07\x1b]*(?:\x07|\x1b\x5c)|\e[\x40-\x5a\x5c-\x5f]/';

    /**
     * Truncate a value with an ellipsis if it exceeds the given width.
     */
    protected function truncate(string $string, int $width): string
    {
        if ($width <= 0) {
            throw new InvalidArgumentException("Width [{$width}] must be greater than zero.");
        }

        if (mb_strwidth($string) <= $width) {
            return $string;
        }

        return $this->withoutTrailingIncompleteEscapeSequence(mb_strimwidth($string, 0, $width - 1).'…');
    }

    /**
     * Remove any trailing escape sequence that was cut in the middle by truncation,
     * as its remainder would otherwise be interpreted as formatting for subsequent output.
     */
    private function withoutTrailingIncompleteEscapeSequence(string $string): string
    {
        if (preg_match_all(self::ESCAPE_SEQUENCE_PATTERN, $string, $matches, PREG_OFFSET_CAPTURE) > 0) {
            $lastCompleteSequence = end($matches[0]);

            $searchFrom = $lastCompleteSequence[1] + strlen($lastCompleteSequence[0]);
        } else {
            $searchFrom = 0;
        }

        $incompletePosition = strpos($string, "\e", $searchFrom);

        return $incompletePosition === false ? $string : substr($string, 0, $incompletePosition);
    }
}
