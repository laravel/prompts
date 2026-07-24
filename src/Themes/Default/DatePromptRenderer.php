<?php

namespace Laravel\Prompts\Themes\Default;

use DateTimeImmutable;
use Laravel\Prompts\DatePrompt;

class DatePromptRenderer extends Renderer
{
    use Concerns\DrawsBoxes;

    /**
     * Render the date prompt.
     */
    public function __invoke(DatePrompt $prompt): string
    {
        $maxWidth = $prompt->terminal()->cols() - 6;

        return match ($prompt->state) {
            'submit' => $this
                ->box(
                    $this->dim($this->truncate($prompt->label, $maxWidth)),
                    $prompt->formattedValue(),
                ),

            'cancel' => $this
                ->box(
                    $this->truncate($prompt->label, $maxWidth),
                    $this->strikethrough($this->dim($prompt->formattedValue())),
                    color: 'red',
                )
                ->error($prompt->cancelMessage),

            'error' => $this
                ->box(
                    $this->truncate($prompt->label, $maxWidth),
                    $this->renderBody($prompt),
                    color: 'yellow',
                )
                ->warning($this->truncate($prompt->error, $prompt->terminal()->cols() - 5)),

            default => $this
                ->box(
                    $this->cyan($this->truncate($prompt->label, $maxWidth)),
                    $this->renderBody($prompt),
                )
                ->when(
                    $prompt->hint,
                    fn () => $this->hint($prompt->hint),
                    fn () => $this->newLine() // Space for errors
                ),
        };
    }

    /**
     * The width of a calendar cell: a three-letter day name or a padded day number.
     */
    protected int $cellWidth = 3;

    /**
     * The width of the calendar grid: seven cells with separators.
     */
    protected int $gridWidth = 27;

    /**
     * Render the selected date above the calendar grid.
     */
    protected function renderBody(DatePrompt $prompt): string
    {
        return implode(PHP_EOL, [
            $prompt->formattedValue(),
            '',
            $this->monthTitle($prompt),
            $this->weekdayHeader($prompt),
            ...$this->weeks($prompt),
        ]);
    }

    /**
     * Render the month title, centered over the grid.
     */
    protected function monthTitle(DatePrompt $prompt): string
    {
        $title = $prompt->date->format('F Y');

        return str_repeat(' ', max(0, intdiv($this->gridWidth - mb_strwidth($title), 2))).$title;
    }

    /**
     * Render the weekday header, starting the week on the prompt's first day.
     */
    protected function weekdayHeader(DatePrompt $prompt): string
    {
        $labels = array_map(
            fn ($day) => (new DateTimeImmutable("Sunday +{$day} days"))->format('D'),
            range($prompt->weekStartsOn, $prompt->weekStartsOn + 6),
        );

        return $this->dim(implode(' ', $labels));
    }

    /**
     * Render the days of the month in rows of seven cells.
     *
     * @return list<string>
     */
    protected function weeks(DatePrompt $prompt): array
    {
        $month = $prompt->date->modify('first day of this month');

        $offset = ((int) $month->format('w') - $prompt->weekStartsOn + 7) % 7;

        $cells = array_fill(0, $offset, str_repeat(' ', $this->cellWidth));

        foreach (range(1, (int) $month->format('t')) as $day) {
            $cells[] = $this->dayCell($prompt, $day);
        }

        return array_map(
            fn ($week) => implode(' ', $week),
            array_chunk($cells, 7),
        );
    }

    /**
     * Render a single day cell.
     */
    protected function dayCell(DatePrompt $prompt, int $day): string
    {
        $cell = str_pad((string) $day, $this->cellWidth, ' ', STR_PAD_LEFT);

        if ($day === (int) $prompt->date->format('j')) {
            return $this->inverse($cell);
        }

        if (! $prompt->selectableDay($day)) {
            return $this->dim($cell);
        }

        return $cell;
    }
}
