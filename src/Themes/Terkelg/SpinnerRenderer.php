<?php

namespace Laravel\Prompts\Themes\Terkelg;

use Laravel\Prompts\Concerns\Colors;
use Laravel\Prompts\Spinner;

class SpinnerRenderer
{
    use Colors;

    protected $frames = ['⠋', '⠙', '⠹', '⠸', '⠼', '⠴', '⠦', '⠧', '⠇', '⠏'];

    protected $interval = 100;

    public function __invoke(Spinner $spinner)
    {
        $spinner->interval = $this->interval;

        $frame = $this->frames[$spinner->count % count($this->frames)];

        return <<<EOT
            {$this->cyan($frame)} {$spinner->message}

            EOT;
    }
}
