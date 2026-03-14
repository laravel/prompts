<?php

namespace Laravel\Prompts\Themes\Default;

use Laravel\Prompts\Box;

class BoxRenderer extends Renderer
{
    use Concerns\DrawsBoxes;

    /**
     * Render the box.
     */
    public function __invoke(Box $box): string
    {
        $this->box(
            $box->title,
            $box->message,
            $box->footer,
            $box->color,
            $box->info,
        );

        return $this;
    }
}
