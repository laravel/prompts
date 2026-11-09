<?php

namespace Laravel\Prompts\Themes\Default;

use Laravel\Prompts\TimePickerPrompt;

class TimePickerPromptRenderer extends Renderer
{
    use Concerns\DrawsBoxes;
    use Concerns\RendersSegments;

    public function __invoke(TimePickerPrompt $prompt): string
    {
        $maxWidth = $prompt->terminal()->cols() - 6;

        return match ($prompt->state) {
            'submit' => $this->box($this->dim($this->truncate($prompt->label, $maxWidth)), $prompt->formattedValue()),
            'cancel' => $this
                ->box($this->truncate($prompt->label, $maxWidth), $this->strikethrough($this->dim($prompt->formattedValue())), color: 'red')
                ->error($prompt->cancelMessage),
            'error' => $this
                ->box($this->truncate($prompt->label, $maxWidth), $this->inputRow($prompt), color: 'yellow')
                ->warning($this->truncate($prompt->error, $prompt->terminal()->cols() - 5)),
            default => $this
                ->box($this->cyan($this->truncate($prompt->label, $maxWidth)), $this->inputRow($prompt))
                ->when($prompt->hint, fn () => $this->hint($prompt->hint), fn () => $this->newLine()),
        };
    }
}
