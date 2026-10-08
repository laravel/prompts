<?php

namespace Laravel\Prompts;

use Closure;
use DateTimeImmutable;
use DateTimeInterface;

class DateTimePickerPrompt extends DatePickerPrompt
{
    use Concerns\InteractsWithTime;

    /**
     * The last time segment focused in calendar mode.
     */
    protected string $timeFocus = 'hour';

    /**
     * Create a new DateTimePickerPrompt instance.
     */
    public function __construct(
        string $label,
        DateTimeInterface|string|null $default = null,
        DateTimeInterface|string|null $min = null,
        DateTimeInterface|string|null $max = null,
        bool|string $required = false,
        mixed $validate = null,
        ?string $hint = null,
        ?Closure $transform = null,
        int $weekStartsOn = 1,
        public bool $withSeconds = false,
        bool $calendar = false,
        public bool $use12Hours = false,
    ) {
        parent::__construct($label, $default, $min, $max, $required, $validate, $hint, $transform, $weekStartsOn, $calendar);

        if ($calendar && $hint === null) {
            $this->hint = 'Tab: calendar/time. Left/Right: segment. Up/Down: change. Type to edit.';
        }

        $time = $this->clamp($this->default ?? $this->truncateTime(new DateTimeImmutable('now')));

        $this->hour = (int) $time->format('G');
        $this->minute = (int) $time->format('i');
        $this->second = (int) $time->format('s');
    }

    /**
     * Get the selected date and time.
     */
    public function value(): ?DateTimeImmutable
    {
        return parent::value()?->setTime($this->hour, $this->minute, $this->second);
    }

    /**
     * Get the selected date and time formatted for display.
     */
    public function formattedValue(): string
    {
        if (! $this->calendar) {
            $segments = $this->segmentValues();

            return $this->formatSegments($segments);
        }

        return parent::formattedValue().' '.$this->formattedTime();
    }

    /** @param array<string, string> $segments */
    public function formatSegments(array $segments): string
    {
        return parent::formatSegments(array_slice($segments, 0, 3)).' '.$this->formatTimeSegments(array_slice($segments, 3));
    }

    /**
     * Handle a key press, routing it to the focused segment.
     */
    protected function handleKey(string $key): mixed
    {
        if ($this->calendar && in_array($key, [Key::TAB, Key::SHIFT_TAB])) {
            if (! $this->commitSegment()) {
                return null;
            }

            if ($this->focused === 'calendar') {
                $this->focused = $this->timeFocus;
            } else {
                $this->timeFocus = $this->focused;
                $this->focused = 'calendar';
            }

            return null;
        }

        return $this->focused === 'calendar' ? parent::handleKey($key) : $this->handleSegmentKey($key);
    }

    /**
     * Step the focused time segment, wrapping around.
     */
    protected function stepSegment(int $step): void
    {
        if (in_array($this->focused, ['year', 'month', 'day'])) {
            parent::stepSegment($step);

            return;
        }

        if (! $this->commitSegment()) {
            return;
        }

        $this->stepTimeSegment($step);
    }

    /**
     * Build the candidate date with the edited segment.
     */
    protected function segmentDate(): ?DateTimeImmutable
    {
        return $this->timeCandidate(($this->calendar ? $this->bufferedDate() : null) ?? parent::segmentDate());
    }

    protected function commitSegment(): bool
    {
        $editing = $this->editingSegment;

        if (! parent::commitSegment()) {
            return false;
        }

        if ($editing) {
            $this->syncTime();

            if ($this->calendar && $this->bufferedDate() !== null) {
                $this->buffer = '';
            }
        }

        return true;
    }

    protected function segmentError(): ?string
    {
        $error = parent::segmentError();

        return $error === 'Invalid date.' && in_array($this->focused, $this->timeSegments())
            ? 'Invalid time.'
            : $error;
    }

    /**
     * The focusable segments.
     *
     * @return list<string>
     */
    protected function segments(): array
    {
        return [
            ...($this->calendar ? [] : parent::segments()),
            ...$this->timeSegments(),
        ];
    }

    /** @return array<string, string> */
    public function segmentValues(): array
    {
        return parent::segmentValues() + $this->timeSegmentValues();
    }

    protected function goTo(DateTimeImmutable $date): void
    {
        parent::goTo($date->setTime($this->hour, $this->minute, $this->second));
        $this->syncTime();
    }

    /**
     * Get the date represented by a completely typed buffer, at the selected time.
     */
    protected function bufferedDate(): ?DateTimeImmutable
    {
        return parent::bufferedDate()?->setTime($this->hour, $this->minute, $this->second);
    }

    /**
     * The format used when displaying dates in messages.
     */
    protected function dateFormat(): string
    {
        return 'Y-m-d '.$this->timeFormat();
    }
}
