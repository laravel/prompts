<?php

namespace Laravel\Prompts\Themes\Default\Concerns;

use Closure;
use Laravel\Prompts\Prompt;

trait RendersDescription
{
    /**
     * Render a description for the given prompt, wrapping it to fit within the calculated box width.
     */
    protected function renderDescription(Prompt $prompt, int $maxWidth, ?Closure $calculateContentWidth = null): string
    {
        if (! property_exists($prompt, 'description') || ! $prompt->description || trim($prompt->description) === '') {
            return '';
        }

        $targetWidth = $calculateContentWidth ? $calculateContentWidth() : $this->calculateDescriptionWidth($prompt, $maxWidth);

        return $this->mbWordwrap($prompt->description, $targetWidth, PHP_EOL);
    }

    /**
     * Calculate the default description width based on title and minWidth.
     */
    protected function calculateDescriptionWidth(Prompt $prompt, int $maxWidth): int
    {
        $titleWidth = mb_strwidth($this->stripEscapeSequences($prompt->label));

        return max($this->minWidth, min($titleWidth, $maxWidth));
    }
}
