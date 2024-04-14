<?php

namespace Laravel\Prompts\Themes\Default;

use Illuminate\Support\Collection;
use Laravel\Prompts\TabbedScrollableSelectPrompt;
use Laravel\Prompts\Themes\Contracts\Scrolling;

class TabbedScrollableSelectRenderer extends Renderer implements Scrolling
{
    use Concerns\DrawsBoxes;
    use Concerns\DrawsScrollbars;
    use Concerns\DrawsTabs;

    /**
     * Render the tabbed-scrollable-select prompt.
     */
    public function __invoke(TabbedScrollableSelectPrompt $prompt): string
    {
        return match ($prompt->state) {
            'submit' => $this
                ->box(
                    $this->truncate($this->dim($prompt->label), $prompt->terminal()->cols() - 6),
                    $this->renderSelectedOption($prompt),
                ),

            'cancel' => $this
                ->box(
                    $this->truncate($prompt->label, $prompt->terminal()->cols() - 6),
                    $this->renderBody($prompt),
                    color: 'red',
                )
                ->error('Cancelled.'),

            'error' => $this
                ->box(
                    $this->truncate($prompt->label, $prompt->terminal()->cols() - 6),
                    $this->renderBody($prompt),
                    color: 'yellow',
                )
                ->warning($this->truncate($prompt->error, $prompt->terminal()->cols() - 5)),

            default => $this
                ->box(
                    title: $this->truncate($prompt->label, $prompt->terminal()->cols() - 6),
                    body: $this->renderBody($prompt),
                    footer: $prompt->hint,
                    // color: 'gray',
                    // info: count($prompt->options) > $prompt->scroll ? (count($prompt->value()).' selected') : '',
                )
                ->when(
                    true,
                    fn () => $this->renderInstructions($prompt)->each(fn($line) => $this->hint($line)),
                    fn () => $this->newLine() // Space for errors
                ),
        };
    }

    /**
     * Render the body.
     */
    protected function renderBody(TabbedScrollableSelectPrompt $prompt): string
    {
        return collect([
            $this->tabs(
                tabs: $prompt->options->pluck('tab'),
                selected: $prompt->selected,
                width: $prompt->width - 6,
            ),
            $this->scrollbar(
                visible: $prompt->visible(),
                firstVisible: $prompt->firstVisible,
                height: $prompt->scroll,
                total: $prompt->content->get($prompt->selected)->count(),
                width: $prompt->width - 6,
            )->implode(PHP_EOL),
        ])->implode(PHP_EOL);
    }

    /**
     * Render the selected options.
     */
    protected function renderSelectedOption(TabbedScrollableSelectPrompt $prompt): string
    {
        return is_null($prompt->selected)
            ? 'No Option Selected'
            : collect([
                $prompt->options->get($prompt->selected)['tab'],
                '',
                ...$prompt->visible()->splice(0, 3),
                '...',
            ])->implode(PHP_EOL);
    }

    /**
     * Render the instructions.
     * 
     * @return \Illuminate\Support\Collection<int, string>
     */
    protected function renderInstructions(TabbedScrollableSelectPrompt $prompt): Collection
    {
        return $prompt->getInstructions()
            ->map(fn($line) => $this->dim($line));
    }

    /**
     * The number of lines to reserve outside of the scrollable area.
     */
    public function reservedLines(): int
    {
        return 5;
    }
}
