<?php

namespace Laravel\Prompts\Themes\Terkelg;

use Laravel\Prompts\Concerns\Colors;
use Laravel\Prompts\Spinner;

class SpinnerRenderer
{
    use Colors;

    public function __invoke(Spinner $spinner)
    {
        return <<<EOT
            {$this->cyan($spinner->frame)} {$spinner->message}

            EOT;
    }
}
