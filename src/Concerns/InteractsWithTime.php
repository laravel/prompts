<?php

namespace Laravel\Prompts\Concerns;

use DateTimeImmutable;

trait InteractsWithTime
{
    public int $hour;

    public int $minute;

    public int $second;

    public function formattedTime(): string
    {
        return $this->date->setTime($this->hour, $this->minute, $this->second)->format($this->timeFormat());
    }

    /** @return list<string> */
    protected function timeSegments(): array
    {
        return [
            'hour', 'minute',
            ...($this->withSeconds ? ['second'] : []),
            ...($this->use12Hours ? ['period'] : []),
        ];
    }

    /** @return array<string, string> */
    protected function timeSegmentValues(): array
    {
        $segments = [
            'hour' => $this->segmentDisplay('hour', sprintf('%02d', $this->use12Hours ? ($this->hour % 12 ?: 12) : $this->hour)),
            'minute' => $this->segmentDisplay('minute', sprintf('%02d', $this->minute)),
        ];

        if ($this->withSeconds) {
            $segments['second'] = $this->segmentDisplay('second', sprintf('%02d', $this->second));
        }

        if ($this->use12Hours) {
            $segments['period'] = $this->segmentDisplay('period', $this->hour < 12 ? 'AM' : 'PM');
        }

        return $segments;
    }

    /** @param array<string, string> $segments */
    public function formatTimeSegments(array $segments): string
    {
        $period = $segments['period'] ?? null;
        unset($segments['period']);

        return implode(':', $segments).($period === null ? '' : ' '.$period);
    }

    protected function segmentInput(string $char): ?string
    {
        if ($this->focused === 'period') {
            return match (strtolower($char)) {
                'a' => 'AM',
                'p' => 'PM',
                'm' => in_array($this->segmentBuffer, ['A', 'P']) ? 'M' : null,
                default => null,
            };
        }

        return ctype_digit($char) ? $char : null;
    }

    protected function timeCandidate(?DateTimeImmutable $date): ?DateTimeImmutable
    {
        $hour = $this->focused === 'hour' ? (int) $this->segmentBuffer : $this->hour;
        $minute = $this->focused === 'minute' ? (int) $this->segmentBuffer : $this->minute;
        $second = $this->focused === 'second' ? (int) $this->segmentBuffer : $this->second;

        if ($this->use12Hours && $this->focused === 'hour') {
            if ($hour < 1 || $hour > 12) {
                return null;
            }

            $hour = $hour % 12 + ($this->hour >= 12 ? 12 : 0);
        }

        if ($this->focused === 'period') {
            if (! in_array($this->segmentBuffer, ['AM', 'PM'])) {
                return null;
            }

            $hour = $hour % 12 + ($this->segmentBuffer === 'PM' ? 12 : 0);
        }

        if ($hour > 23 || $minute > 59 || $second > 59) {
            return null;
        }

        return $date === null ? null : $this->timeAt($date, $hour, $minute, $second);
    }

    protected function timeAt(DateTimeImmutable $date, int $hour, int $minute, int $second): ?DateTimeImmutable
    {
        return $date->setTime($hour, $minute, $second);
    }

    protected function stepTimeSegment(int $step): void
    {
        match ($this->focused) {
            'hour' => $this->hour = $this->use12Hours
                ? ($this->hour % 12 + $step + 12) % 12 + ($this->hour >= 12 ? 12 : 0)
                : ($this->hour + $step + 24) % 24,
            'minute' => $this->minute = ($this->minute + $step + 60) % 60,
            'second' => $this->second = ($this->second + $step + 60) % 60,
            'period' => $this->hour = ($this->hour + 12) % 24,
            default => null,
        };
    }

    protected function syncTime(): void
    {
        $this->hour = (int) $this->date->format('G');
        $this->minute = (int) $this->date->format('i');
        $this->second = (int) $this->date->format('s');
    }

    protected function truncateTime(DateTimeImmutable $date): DateTimeImmutable
    {
        return $this->withSeconds
            ? $date->setTime((int) $date->format('G'), (int) $date->format('i'), (int) $date->format('s'))
            : $date->setTime((int) $date->format('G'), (int) $date->format('i'));
    }

    protected function timeFormat(): string
    {
        return ($this->use12Hours ? 'h:i' : 'H:i').($this->withSeconds ? ':s' : '').($this->use12Hours ? ' A' : '');
    }
}
