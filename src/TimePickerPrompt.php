<?php

namespace Laravel\Prompts;

use Closure;
use DateTimeImmutable;
use DateTimeInterface;
use Exception;
use InvalidArgumentException;
use RuntimeException;

class TimePickerPrompt extends Prompt
{
    use Concerns\InteractsWithSegments {
        commitSegment as protected commitTimeSegment;
    }
    use Concerns\InteractsWithTime {
        Concerns\InteractsWithTime::segmentInput insteadof Concerns\InteractsWithSegments;
    }

    public DateTimeImmutable $date;

    public ?DateTimeImmutable $default;

    public ?DateTimeImmutable $min;

    public ?DateTimeImmutable $max;

    public string $hint;

    public function __construct(
        public string $label,
        DateTimeInterface|string|null $default = null,
        DateTimeInterface|string|null $min = null,
        DateTimeInterface|string|null $max = null,
        public bool|string $required = false,
        public mixed $validate = null,
        ?string $hint = null,
        public ?Closure $transform = null,
        public bool $withSeconds = false,
        public bool $use12Hours = false,
    ) {
        $this->default = $this->normalizeTime($default);
        $this->min = $this->normalizeTime($min);
        $this->max = $this->normalizeTime($max);

        if ($this->min !== null && $this->max !== null && $this->min->format('H:i:s') > $this->max->format('H:i:s')) {
            throw new InvalidArgumentException('Argument [min] must be on or before [max].');
        }

        $this->date = $this->clamp($this->default ?? $this->truncateTime(new DateTimeImmutable('now')));
        $this->syncTime();
        $this->focused = 'hour';
        $this->hint = $hint ?? 'Left/Right or Tab: move. Up/Down: change. Type to edit.';
        $this->validate = $this->wrapValidation($this->validate);

        $this->on('key', fn ($key) => $this->handleSegmentKey($key));
    }

    public function value(): ?DateTimeImmutable
    {
        return static::$interactive === false ? $this->default : $this->date->setTime($this->hour, $this->minute, $this->second);
    }

    public function formattedValue(): string
    {
        return $this->formatSegments($this->segmentValues());
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

    protected function segmentDate(): ?DateTimeImmutable
    {
        return $this->timeCandidate($this->date);
    }

    protected function segmentError(): ?string
    {
        if (! $this->editingSegment) {
            return null;
        }

        if ($this->segmentBuffer === '') {
            return "Incomplete {$this->focused}.";
        }

        return $this->segmentDate() === null ? 'Invalid time.' : null;
    }

    protected function commitSegment(): bool
    {
        if (! $this->commitTimeSegment()) {
            return false;
        }

        $this->syncTime();

        return true;
    }

    protected function stepSegment(int $step): void
    {
        if (! $this->commitSegment()) {
            return;
        }

        $this->stepTimeSegment($step);
        $candidate = $this->timeAt($this->date, $this->hour, $this->minute, $this->second);

        if ($candidate === null) {
            $this->syncTime();
            $this->state = 'error';
            $this->error = 'Invalid time.';

            return;
        }

        try {
            $this->date = $this->clamp($candidate);
        } catch (InvalidArgumentException $e) {
            $this->state = 'error';
            $this->error = $e->getMessage();
        }

        $this->syncTime();
    }

    protected function timeAt(DateTimeImmutable $date, int $hour, int $minute, int $second): ?DateTimeImmutable
    {
        $candidate = $date->setTime($hour, $minute, $second);
        $expected = $date->format('Y-m-d').' '.sprintf('%02d:%02d:%02d', $hour, $minute, $second);

        return $candidate->format('Y-m-d H:i:s') === $expected ? $candidate : null;
    }

    protected function submit(): void
    {
        if ($this->commitSegment()) {
            parent::submit();
        }
    }

    protected function normalizeTime(DateTimeInterface|string|null $time): ?DateTimeImmutable
    {
        if ($time === null) {
            return null;
        }

        if ($time instanceof DateTimeInterface) {
            return $this->truncateTime(DateTimeImmutable::createFromInterface($time));
        }

        try {
            $date = new DateTimeImmutable($time);
            $errors = DateTimeImmutable::getLastErrors();

            if ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0)) {
                throw new InvalidArgumentException();
            }

            return $this->truncateTime($date);
        } catch (Exception) {
            throw new InvalidArgumentException("Time [{$time}] is not valid.");
        }
    }

    protected function clamp(DateTimeImmutable $time): DateTimeImmutable
    {
        $bound = match (true) {
            $this->min !== null && $time->format('H:i:s') < $this->min->format('H:i:s') => $this->min,
            $this->max !== null && $time->format('H:i:s') > $this->max->format('H:i:s') => $this->max,
            default => null,
        };

        if ($bound === null) {
            return $time;
        }

        $candidate = $this->timeAt($time, (int) $bound->format('G'), (int) $bound->format('i'), (int) $bound->format('s'));

        if ($candidate === null) {
            throw new InvalidArgumentException('Time ['.$bound->format('H:i:s').'] cannot be represented on '.$time->format('Y-m-d').' in '.$time->getTimezone()->getName().'.');
        }

        return $candidate;
    }

    protected function rangeError(DateTimeImmutable $time): ?string
    {
        return match (true) {
            $this->min !== null && $time->format('H:i:s') < $this->min->format('H:i:s') => 'Must be on or after '.$this->min->format($this->timeFormat()).'.',
            $this->max !== null && $time->format('H:i:s') > $this->max->format('H:i:s') => 'Must be on or before '.$this->max->format($this->timeFormat()).'.',
            default => null,
        };
    }

    protected function wrapValidation(mixed $validate): callable
    {
        return function ($value) use ($validate) {
            if (($error = $this->segmentError()) !== null) {
                return $error;
            }

            if (($selected = $this->value()) !== null && ($error = $this->rangeError($selected)) !== null) {
                return $error;
            }

            if (! $validate && ! isset(static::$validateUsing)) {
                return null;
            }

            if (is_callable($validate)) {
                return $validate($value);
            }

            if (! isset(static::$validateUsing)) {
                throw new RuntimeException('The validation logic is missing.');
            }

            $wrapped = $this->validate;
            $this->validate = $validate;

            try {
                return (static::$validateUsing)($this);
            } finally {
                $this->validate = $wrapped;
            }
        };
    }

    protected function isInvalidWhenRequired(mixed $value): bool
    {
        return $value === null;
    }
}
