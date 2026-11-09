<?php

namespace Laravel\Prompts\Concerns;

trait RendersAnimations
{
    /**
     * Determine whether the prompt should be rendered statically.
     *
     * Animating redraws the prompt with cursor movements, which only make
     * sense on a terminal. Decorated output alone is not enough, because
     * `--ansi` forces it in CI and build logs where each frame would be
     * written on its own line.
     */
    protected function shouldRenderStatically(): bool
    {
        return ! static::output()->isDecorated()
            || ! static::terminal()->isOutputTty(static::output())
            || ! function_exists('pcntl_fork')
            || ! function_exists('posix_kill');
    }
}
