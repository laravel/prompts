<?php

namespace Laravel\Prompts\Themes\Default;

use Laravel\Prompts\DatePickerPrompt;

class DatePickerPromptRenderer extends Renderer
{
    use Concerns\DrawsBoxes;
    use Concerns\RendersSegments;

    /**
     * Render the date prompt.
     */
    public function __invoke(DatePickerPrompt $prompt): string
    {
        $maxWidth = $prompt->terminal()->cols() - 6;
        $weekday = $prompt->previewDate()->format('l');

        return match ($prompt->state) {
            'submit' => $this
                ->box(
                    $this->dim($this->truncate($prompt->label, $maxWidth)),
                    $prompt->formattedValue(),
                    info: $weekday,
                ),

            'cancel' => $this
                ->box(
                    $this->truncate($prompt->label, $maxWidth),
                    $this->strikethrough($this->dim($prompt->formattedValue())),
                    color: 'red',
                    info: $weekday,
                )
                ->error($prompt->cancelMessage),

            'error' => $this
                ->box(
                    $this->truncate($prompt->label, $maxWidth),
                    $this->inputRow($prompt),
                    color: 'yellow',
                    info: $weekday,
                )
                ->warning($this->truncate($prompt->error, $prompt->terminal()->cols() - 5)),

            default => $this
                ->box(
                    $this->cyan($this->truncate($prompt->label, $maxWidth)),
                    $this->inputRow($prompt),
                    info: $weekday,
                )
                ->when(
                    $prompt->hint,
                    fn () => $this->hint($prompt->hint),
                    fn () => $this->newLine() // Space for errors
                ),
        };
    }
}
