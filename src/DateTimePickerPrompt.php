<?php

namespace Laravel\Prompts;

use Closure;
use DateTimeImmutable;
use DateTimeInterface;

class DateTimePickerPrompt extends DatePickerPrompt
{
    use Concerns\InteractsWithTime;

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
        public bool $withSeconds = false,
        public bool $use12Hours = false,
    ) {
        parent::__construct($label, $default, $min, $max, $required, $validate, $hint, $transform);

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

    /** @param array<string, string> $segments */
    public function formatSegments(array $segments): string
    {
        return parent::formatSegments(array_slice($segments, 0, 3)).' '.$this->formatTimeSegments(array_slice($segments, 3));
    }

    /**
     * Step the focused segment, wrapping time segments around.
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

        [$hour, $minute, $second] = [$this->hour, $this->minute, $this->second];

        $this->stepTimeSegment($step);

        $candidate = $this->timeAt($this->date, $this->hour, $this->minute, $this->second);

        if ($candidate === null) {
            [$this->hour, $this->minute, $this->second] = [$hour, $minute, $second];
            $this->state = 'error';
            $this->error = 'Invalid time.';

            return;
        }

        $this->goTo($candidate);
    }

    /**
     * Build the candidate date with the edited segment.
     */
    protected function segmentDate(): ?DateTimeImmutable
    {
        return $this->timeCandidate(parent::segmentDate());
    }

    protected function commitSegment(): bool
    {
        $editing = $this->editingSegment;

        if (! parent::commitSegment()) {
            return false;
        }

        if ($editing) {
            $this->syncTime();
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
        return [...parent::segments(), ...$this->timeSegments()];
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
     * The format used when displaying dates in messages.
     */
    protected function dateFormat(): string
    {
        return 'Y-m-d '.$this->timeFormat();
    }
}
