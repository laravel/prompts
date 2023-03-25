<?php

namespace Laravel\Prompts\Themes\Clack;

use Laravel\Prompts\Concerns\Colors;
use Laravel\Prompts\Spinner;

class SpinnerRenderer
{
    use Colors;

    protected $frames = ['◐', '◓', '◑', '◒'];

    protected $interval = 100;

    public function __invoke(Spinner $spinner)
    {
        $spinner->interval = $this->interval;

        $frame = $this->frames[$spinner->count % count($this->frames)];

        return <<<EOT
            {$this->gray('│')}
            {$this->magenta($frame)}  {$spinner->message}

            EOT;
    }
}
