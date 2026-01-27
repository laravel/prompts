<?php

namespace Laravel\Prompts\Themes\Default;

use Laravel\Prompts\NumberPrompt;

class NumberPromptRenderer extends Renderer
{
    use Concerns\DrawsBoxes;

    protected $upArrow = '▲';

    protected $downArrow = '▼';

    /**
     * Render the number prompt.
     */
    public function __invoke(NumberPrompt $prompt): string
    {
        $maxWidth = $prompt->terminal()->cols() - 6;

        return match ($prompt->state) {
            'submit' => $this
                ->box(
                    $this->dim($this->truncate($prompt->label, $prompt->terminal()->cols() - 6)),
                    $this->truncate($prompt->value(), $maxWidth),
                ),

            'cancel' => $this
                ->box(
                    $this->truncate($prompt->label, $prompt->terminal()->cols() - 6),
                    $this->strikethrough($this->dim($this->truncate($prompt->value() ?: $prompt->placeholder, $maxWidth))),
                    color: 'red',
                )
                ->error('Cancelled.'),

            'error' => $this
                ->box(
                    $this->truncate($prompt->label, $prompt->terminal()->cols() - 6),
                    $this->withArrows($prompt, $prompt->valueWithCursor($maxWidth), 'yellow'),
                    color: 'yellow',
                )
                ->warning($this->truncate($prompt->error, $prompt->terminal()->cols() - 5)),

            default => $this
                ->box(
                    $this->cyan($this->truncate($prompt->label, $prompt->terminal()->cols() - 6)),
                    $this->withArrows($prompt, $prompt->valueWithCursor($maxWidth)),
                )
                ->when(
                    $prompt->hint,
                    fn() => $this->hint($prompt->hint),
                    fn() => $this->newLine() // Space for errors
                )
        };
    }

    protected function withArrows(NumberPrompt $prompt, string $value): string
    {
        $arrows = $this->getArrows($prompt);
        $valueLength = mb_strwidth($this->stripEscapeSequences($value));
        $padding = $this->minWidth - $valueLength - mb_strwidth($this->stripEscapeSequences($arrows));

        return $value . str_repeat(' ', $padding) . $arrows;
    }

    protected function getArrows(NumberPrompt $prompt, ?string $color = null): string
    {
        $upArrow = $this->upArrow;
        $downArrow = $this->downArrow;

        if ($color) {
            $upArrow = $this->{$color}($upArrow);
            $downArrow = $this->{$color}($downArrow);
        }

        if (is_numeric($prompt->value())) {
            if ((int) $prompt->value() === $prompt->min) {
                $downArrow = $this->dim($downArrow);
            }

            if ((int) $prompt->value() === $prompt->max) {
                $upArrow = $this->dim($upArrow);
            }

            return $upArrow .  $downArrow;
        }

        if ($prompt->value() === '') {
            return $upArrow . $downArrow;
        }

        return $this->dim($upArrow) . $this->dim($downArrow);
    }
}
