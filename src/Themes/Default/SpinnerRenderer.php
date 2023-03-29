<?php

namespace Laravel\Prompts\Themes\Default;

use Laravel\Prompts\Concerns\Colors;
use Laravel\Prompts\Spinner;

class SpinnerRenderer
{
    use Colors;

    /**
     * The frames of the spinner.
     *
     * @var array<string>
     */
    protected array $frames = ['⠂', '⠒', '⠐', '⠰', '⠠', '⠤', '⠄', '⠆'];

    /**
     * The frame to render when the spinner is static.
     */
    protected string $staticFrame = '⠶';

    /**
     * The interval between frames.
     */
    protected int $interval = 75;

    /**
     * Render the spinner.
     */
    public function __invoke(Spinner $spinner): string
    {
        if ($spinner->static) {
            return <<<EOT

                 {$this->cyan($this->staticFrame)} {$spinner->message}

                EOT;
        }

        $spinner->interval = $this->interval;

        $frame = $this->frames[$spinner->count % count($this->frames)];

        return <<<EOT

             {$this->cyan($frame)} {$spinner->message}

            EOT;
    }
}
