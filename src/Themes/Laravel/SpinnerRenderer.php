<?php

namespace Laravel\Prompts\Themes\Laravel;

use Laravel\Prompts\Concerns\Colors;
use Laravel\Prompts\Spinner;

class SpinnerRenderer
{
    use Colors;

    public function __invoke(Spinner $spinner)
    {
        return <<<EOT

             {$this->magenta($spinner->frame)}  {$spinner->message}

            EOT;
    }
}
