<?php

namespace Laravel\Prompts\Themes\Default;

use Laravel\Prompts\ConfirmPrompt;
use Laravel\Prompts\Prompt;

class ConfirmPromptRenderer extends Renderer
{
    use Concerns\DrawsBoxes;
    use Concerns\RendersDescription;

    /**
     * Render the confirm prompt.
     */
    public function __invoke(ConfirmPrompt $prompt): string
    {
        $maxWidth = $prompt->terminal()->cols() - 6;
        $hasDescription = $prompt->description && trim($prompt->description) !== '';

        return match ($prompt->state) {
            'submit' => $this
                ->box(
                    $this->dim($this->truncate($prompt->label, $prompt->terminal()->cols() - 6)),
                    $hasDescription ? $this->renderDescription($prompt, $maxWidth, fn () => $this->calculateDescriptionWidth($prompt, $maxWidth)) : $this->truncate($prompt->label(), $prompt->terminal()->cols() - 6),
                    $hasDescription ? $this->truncate($prompt->label(), $prompt->terminal()->cols() - 6) : '',
                ),

            'cancel' => $this
                ->box(
                    $this->truncate($prompt->label, $prompt->terminal()->cols() - 6),
                    $hasDescription ? $this->renderDescription($prompt, $maxWidth, fn () => $this->calculateDescriptionWidth($prompt, $maxWidth)) : $this->renderOptions($prompt),
                    $hasDescription ? $this->renderOptions($prompt) : '',
                    color: 'red'
                )
                ->error($prompt->cancelMessage),

            'error' => $this
                ->box(
                    $this->truncate($prompt->label, $prompt->terminal()->cols() - 6),
                    $hasDescription ? $this->renderDescription($prompt, $maxWidth, fn () => $this->calculateDescriptionWidth($prompt, $maxWidth)) : $this->renderOptions($prompt),
                    $hasDescription ? $this->renderOptions($prompt) : '',
                    color: 'yellow',
                )
                ->warning($this->truncate($prompt->error, $prompt->terminal()->cols() - 5)),

            default => $this
                ->box(
                    $this->cyan($this->truncate($prompt->label, $prompt->terminal()->cols() - 6)),
                    $hasDescription ? $this->renderDescription($prompt, $maxWidth, fn () => $this->calculateDescriptionWidth($prompt, $maxWidth)) : $this->renderOptions($prompt),
                    $hasDescription ? $this->renderOptions($prompt) : '',
                )
                ->when(
                    $prompt->hint,
                    fn () => $this->hint($prompt->hint),
                    fn () => $this->newLine() // Space for errors
                ),
        };
    }

    /**
     * Calculate the description width based on buttons.
     */
    protected function calculateDescriptionWidth(Prompt $prompt, int $maxWidth): int
    {
        if (! $prompt instanceof ConfirmPrompt) {
            return $this->minWidth;
        }

        $titleWidth = mb_strwidth($this->stripEscapeSequences($prompt->label));
        $buttonsWidth = mb_strwidth($prompt->yes.' / '.$prompt->no) + 10; // padding for buttons

        return max($this->minWidth, max($titleWidth, min($buttonsWidth, $maxWidth)));
    }

    /**
     * Render the confirm prompt options.
     */
    protected function renderOptions(ConfirmPrompt $prompt): string
    {
        $length = (int) floor(($prompt->terminal()->cols() - 14) / 2);
        $yes = $this->truncate($prompt->yes, $length);
        $no = $this->truncate($prompt->no, $length);

        if ($prompt->state === 'cancel') {
            return $this->dim($prompt->confirmed
                ? "● {$this->strikethrough($yes)} / ○ {$this->strikethrough($no)}"
                : "○ {$this->strikethrough($yes)} / ● {$this->strikethrough($no)}");
        }

        return $prompt->confirmed
            ? "{$this->green('●')} {$yes} {$this->dim('/ ○ '.$no)}"
            : "{$this->dim('○ '.$yes.' /')} {$this->green('●')} {$no}";
    }
}
