<?php

namespace Laravel\Prompts\Themes\Laravel;

use Laravel\Prompts\Concerns\Colors;
use Laravel\Prompts\Spinner;

class SpinnerRenderer
{
    use Colors;

    protected $frames = ['⠂', '⠒', '⠐', '⠰', '⠠', '⠤', '⠄', '⠆'];

    protected $staticFrame = '⠶';

    protected $interval = 75;

    public function __invoke(Spinner $spinner)
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
