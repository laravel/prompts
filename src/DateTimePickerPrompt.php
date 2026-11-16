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
    }

    /** @return array<string, string> */
    public function segmentValues(): array
    {
        return parent::segmentValues() + $this->timeSegmentValues();
    }

    /** @param array<string, string> $segments */
    public function formatSegments(array $segments): string
    {
        return parent::formatSegments(array_slice($segments, 0, 3)).' '.$this->formatTimeSegments(array_slice($segments, 3));
    }

    /** @return list<string> */
    protected function segments(): array
    {
        return [...parent::segments(), ...$this->timeSegments()];
    }

    protected function stepSegment(int $step): void
    {
        if (in_array($this->focused, $this->timeSegments())) {
            $this->stepTime($step);
        } else {
            parent::stepSegment($step);
        }
    }

    protected function segmentDate(): ?DateTimeImmutable
    {
        return $this->timeCandidate(parent::segmentDate());
    }

    protected function displayFormat(): string
    {
        return 'Y-m-d '.$this->timeFormat();
    }
}
