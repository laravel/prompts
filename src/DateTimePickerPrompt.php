<?php

namespace Laravel\Prompts;

use Closure;
use DateTimeImmutable;
use DateTimeInterface;

class DateTimePickerPrompt extends DatePickerPrompt
{
    /**
     * The hour of the selected time.
     */
    public int $hour;

    /**
     * The minute of the selected time.
     */
    public int $minute;

    /**
     * The second of the selected time.
     */
    public int $second;

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

            return implode('-', array_slice($segments, 0, 3)).' '.implode(':', array_slice($segments, 3));
        }

        return parent::formattedValue().' '.$this->formattedTime();
    }

    /**
     * Get the selected time formatted for display.
     */
    public function formattedTime(): string
    {
        return $this->withSeconds
            ? sprintf('%02d:%02d:%02d', $this->hour, $this->minute, $this->second)
            : sprintf('%02d:%02d', $this->hour, $this->minute);
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

        match ($this->focused) {
            'hour' => $this->hour = ($this->hour + $step + 24) % 24,
            'minute' => $this->minute = ($this->minute + $step + 60) % 60,
            'second' => $this->second = ($this->second + $step + 60) % 60,
            default => null,
        };
    }

    /**
     * Build the candidate date with the edited segment.
     */
    protected function segmentDate(): ?DateTimeImmutable
    {
        $date = ($this->calendar ? $this->bufferedDate() : null) ?? parent::segmentDate();
        $hour = $this->focused === 'hour' ? (int) $this->segmentBuffer : $this->hour;
        $minute = $this->focused === 'minute' ? (int) $this->segmentBuffer : $this->minute;
        $second = $this->focused === 'second' ? (int) $this->segmentBuffer : $this->second;

        if ($hour > 23 || $minute > 59 || $second > 59) {
            return null;
        }

        return $date?->setTime($hour, $minute, $second);
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

        return $error === 'Invalid date.' && in_array($this->focused, ['hour', 'minute', 'second'])
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
            'hour', 'minute',
            ...($this->withSeconds ? ['second'] : []),
        ];
    }

    /** @return array<string, string> */
    public function segmentValues(): array
    {
        $segments = parent::segmentValues() + [
            'hour' => $this->segmentDisplay('hour', sprintf('%02d', $this->hour)),
            'minute' => $this->segmentDisplay('minute', sprintf('%02d', $this->minute)),
        ];

        if ($this->withSeconds) {
            $segments['second'] = $this->segmentDisplay('second', sprintf('%02d', $this->second));
        }

        return $segments;
    }

    protected function goTo(DateTimeImmutable $date): void
    {
        parent::goTo($date->setTime($this->hour, $this->minute, $this->second));
        $this->syncTime();
    }

    protected function syncTime(): void
    {
        $this->hour = (int) $this->date->format('G');
        $this->minute = (int) $this->date->format('i');
        $this->second = (int) $this->date->format('s');
    }

    /**
     * Get the date represented by a completely typed buffer, at the selected time.
     */
    protected function bufferedDate(): ?DateTimeImmutable
    {
        return parent::bufferedDate()?->setTime($this->hour, $this->minute, $this->second);
    }

    /**
     * Truncate the time to the prompt's precision.
     */
    protected function truncateTime(DateTimeImmutable $date): DateTimeImmutable
    {
        return $this->withSeconds
            ? $date->setTime((int) $date->format('G'), (int) $date->format('i'), (int) $date->format('s'))
            : $date->setTime((int) $date->format('G'), (int) $date->format('i'));
    }

    /**
     * The format used when displaying dates in messages.
     */
    protected function dateFormat(): string
    {
        return $this->withSeconds ? 'Y-m-d H:i:s' : 'Y-m-d H:i';
    }
}
