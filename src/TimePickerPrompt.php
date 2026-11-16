<?php

namespace Laravel\Prompts;

use Closure;
use DateTimeImmutable;
use DateTimeInterface;
use InvalidArgumentException;

class TimePickerPrompt extends TimeBasedPrompt
{
    use Concerns\InteractsWithTime;

    protected string $noun = 'Time';

    /**
     * Create a new TimePickerPrompt instance.
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
    }

    /** @return array<string, string> */
    public function segmentValues(): array
    {
        return $this->timeSegmentValues();
    }

    /** @param array<string, string> $segments */
    public function formatSegments(array $segments): string
    {
        return $this->formatTimeSegments($segments);
    }

    /** @return list<string> */
    protected function segments(): array
    {
        return $this->timeSegments();
    }

    protected function stepSegment(int $step): void
    {
        $this->stepTime($step);
    }

    protected function segmentDate(): ?DateTimeImmutable
    {
        return $this->timeCandidate($this->date);
    }

    /**
     * Compare clock times only, so the bounds apply on any date.
     */
    protected function compare(DateTimeImmutable $a, DateTimeImmutable $b): int
    {
        return $a->format('H:i:s') <=> $b->format('H:i:s');
    }

    /**
     * Move the time onto the bound's clock time while keeping its own date.
     */
    protected function clamp(DateTimeImmutable $time): DateTimeImmutable
    {
        $bound = parent::clamp($time);

        if ($bound === $time) {
            return $time;
        }

        $candidate = $this->timeAt($time, (int) $bound->format('G'), (int) $bound->format('i'), (int) $bound->format('s'));

        if ($candidate === null) {
            throw new InvalidArgumentException('Time ['.$bound->format('H:i:s').'] cannot be represented on '.$time->format('Y-m-d').' in '.$time->getTimezone()->getName().'.');
        }

        return $candidate;
    }

    protected function displayFormat(): string
    {
        return $this->timeFormat();
    }
}
