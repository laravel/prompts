<?php

namespace Laravel\Prompts\Themes\Default\Concerns;

use Illuminate\Support\Collection;
use Laravel\Prompts\ViewState;

trait DrawsScrollbars
{
    /**
     * Scroll the given lines.
     *
     * @param  \Illuminate\Support\Collection<int, string>  $lines
     * @param  \Laravel\Prompts\ViewState  $view
     * @return \Illuminate\Support\Collection<int, string>
     */
    protected function scroll(Collection $lines, ?int $focused, ViewState $view, int $height, int $width, string $color = 'cyan'): Collection
    {
        if ($lines->count() <= $height) {
            return $lines;
        }

        $visible = $this->visible($lines, $focused, $view, $height);

        return $visible
            ->map(fn ($line) => $this->pad($line, $width))
            ->map(fn ($line, $index) => match (true) {
                $index === $this->scrollPosition($visible, $focused, $height, $lines->count()) => preg_replace('/.$/', $this->{$color}('┃'), $line),
                default => preg_replace('/.$/', $this->gray('│'), $line),
            });
    }

    /**
     * Get a scrolled version of the items and update the view state.
     *
     * @param  \Illuminate\Support\Collection<int, string>  $lines
     * @param  \Laravel\Prompts\ViewState  $view
     * @return \Illuminate\Support\Collection<int, string>
     */
    protected function visible(Collection $lines, ?int $focused, ViewState $view, int $height): Collection
    {
        if ($lines->count() <= $height) {
            return $lines;
        }

        if ($focused === null) {
            $view->resetStart();
            return $lines->slice(0, $height);
        }

        if ($focused < $view->first()) {
            $view->update($focused);
            return $lines->slice($view->first(), count($view->items));
        }

        if ($focused > $view->last()) {
            $view->update($focused - $height + 1);
            return $lines->slice($view->first(), count($view->items));
        }

        return $lines->slice($view->first(), count($view->items));
    }

    /**
     * Scroll the given lines.
     *
     * @param  \Illuminate\Support\Collection<int, string>  $visible
     */
    protected function scrollPosition(Collection $visible, ?int $focused, int $height, int $total): int
    {
        if ($focused < $height) {
            return 0;
        }

        if ($focused === $total - 1) {
            return $total - 1;
        }

        $percent = ($focused + 1 - $height) / ($total - $height);

        $keys = $visible->slice(1, -1)->keys();
        $position = (int) ceil($percent * count($keys) - 1);

        return $keys[$position] ?? 0;
    }
}
