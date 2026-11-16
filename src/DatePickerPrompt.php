<?php

namespace Laravel\Prompts;

use Closure;
use DateTimeImmutable;
use DateTimeInterface;
use Exception;
use InvalidArgumentException;
use RuntimeException;

class DatePickerPrompt extends Prompt
{
    use Concerns\InteractsWithSegments;

    /**
     * The selected date.
     */
    public DateTimeImmutable $date;

    /**
     * The default date.
     */
    public ?DateTimeImmutable $default;

    /**
     * The earliest selectable date.
     */
    public ?DateTimeImmutable $min;

    /**
     * The latest selectable date.
     */
    public ?DateTimeImmutable $max;

    public string $hint;

    /**
     * Create a new DatePickerPrompt instance.
     */
    public function __construct(
        public string $label,
        DateTimeInterface|string|null $default = null,
        DateTimeInterface|string|null $min = null,
        DateTimeInterface|string|null $max = null,
        public bool|string $required = false,
        public mixed $validate = null,
        ?string $hint = null,
        public ?Closure $transform = null,
    ) {
        $this->default = $this->normalizeDate($default);
        $this->min = $this->normalizeDate($min);
        $this->max = $this->normalizeDate($max);

        if ($this->min !== null && $this->max !== null && $this->min > $this->max) {
            throw new InvalidArgumentException('Argument [min] must be on or before [max].');
        }

        $this->date = $this->clamp($this->default ?? new DateTimeImmutable('today'));

        $this->focused = 'year';
        $this->hint = $hint ?? 'Left/Right or Tab: move. Up/Down: change. Type to edit.';

        $this->validate = $this->wrapValidation($this->validate);

        $this->on('key', fn ($key) => $this->handleSegmentKey($key));
    }

    /**
     * Get the selected date.
     */
    public function value(): ?DateTimeImmutable
    {
        if (static::$interactive === false) {
            return $this->default;
        }

        return $this->date;
    }

    /**
     * Get the selected date formatted for display.
     */
    public function formattedValue(): string
    {
        return $this->formatSegments($this->segmentValues());
    }

    /**
     * Get the date being edited, including a valid segment that hasn't been committed yet.
     */
    public function previewDate(): DateTimeImmutable
    {
        if ($this->editingSegment && $this->segmentError() === null) {
            return $this->segmentDate() ?? $this->date;
        }

        return $this->date;
    }

    /** @return array<string, string> */
    public function segmentValues(): array
    {
        return [
            'year' => $this->segmentDisplay('year', $this->date->format('Y')),
            'month' => $this->segmentDisplay('month', $this->date->format('m')),
            'day' => $this->segmentDisplay('day', $this->date->format('d')),
        ];
    }

    /** @param array<string, string> $segments */
    public function formatSegments(array $segments): string
    {
        return implode('-', $segments);
    }

    /** @return list<string> */
    protected function segments(): array
    {
        return ['year', 'month', 'day'];
    }

    protected function stepSegment(int $step): void
    {
        if (! $this->commitSegment()) {
            return;
        }

        $date = match ($this->focused) {
            'year' => $this->addMonths($step * 12),
            'month' => $this->addMonths($step),
            default => $this->date->modify(sprintf('%+d days', $step)),
        };

        if ((int) $date->format('Y') >= 1 && (int) $date->format('Y') <= 9999) {
            $this->goTo($date);
        }
    }

    protected function segmentDate(): ?DateTimeImmutable
    {
        $year = (int) $this->date->format('Y');
        $month = (int) $this->date->format('m');
        $day = (int) $this->date->format('d');

        match ($this->focused) {
            'year' => $year = (int) $this->segmentBuffer,
            'month' => $month = (int) $this->segmentBuffer,
            'day' => $day = (int) $this->segmentBuffer,
            default => null,
        };

        if ($year < 1 || $year > 9999 || $month < 1 || $month > 12) {
            return null;
        }

        if ($this->focused !== 'day') {
            $day = min($day, (int) $this->date->setDate($year, $month, 1)->format('t'));
        }

        return checkdate($month, $day, $year) ? $this->date->setDate($year, $month, $day) : null;
    }

    protected function segmentError(): ?string
    {
        if (! $this->editingSegment) {
            return null;
        }

        if ($this->segmentBuffer === '' || ($this->focused === 'year' && strlen($this->segmentBuffer) < 4)) {
            return "Incomplete {$this->focused}.";
        }

        return $this->segmentDate() === null ? 'Invalid date.' : null;
    }

    protected function submit(): void
    {
        if ($this->commitSegment()) {
            parent::submit();
        }
    }

    /**
     * Highlight the given date, keeping it within the min/max range.
     */
    protected function goTo(DateTimeImmutable $date): void
    {
        $this->date = $this->clamp($date);
    }

    /**
     * Wrap the validation logic to first verify the edited segment and the range.
     */
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

            return match (true) {
                is_callable($validate) => $validate($value),
                isset(static::$validateUsing) => $this->delegateValidation($validate),
                default => throw new RuntimeException('The validation logic is missing.'),
            };
        };
    }

    /**
     * Delegate to the custom validation callback, exposing the original validation logic.
     */
    protected function delegateValidation(mixed $validate): mixed
    {
        $wrapped = $this->validate;

        $this->validate = $validate;

        try {
            return (static::$validateUsing)($this);
        } finally {
            $this->validate = $wrapped;
        }
    }

    /**
     * Get the validation error for a date outside of the min/max range.
     */
    protected function rangeError(DateTimeImmutable $date): ?string
    {
        return match (true) {
            $this->min !== null && $date < $this->min => 'Must be on or after '.$this->min->format($this->dateFormat()).'.',
            $this->max !== null && $date > $this->max => 'Must be on or before '.$this->max->format($this->dateFormat()).'.',
            default => null,
        };
    }

    /**
     * The format used when displaying dates in messages.
     */
    protected function dateFormat(): string
    {
        return 'Y-m-d';
    }

    /**
     * Add the given number of months, clamping the day to the target month.
     */
    protected function addMonths(int $months): DateTimeImmutable
    {
        $month = $this->date->modify('first day of this month')->modify(sprintf('%+d months', $months));

        $day = min((int) $this->date->format('j'), (int) $month->format('t'));

        return $month->setDate((int) $month->format('Y'), (int) $month->format('n'), $day);
    }

    /**
     * Constrain the given date to the min/max range.
     */
    protected function clamp(DateTimeImmutable $date): DateTimeImmutable
    {
        return match (true) {
            $this->min !== null && $date < $this->min => $this->min,
            $this->max !== null && $date > $this->max => $this->max,
            default => $date,
        };
    }

    /**
     * Normalize the given date to a DateTimeImmutable at the prompt's precision.
     */
    protected function normalizeDate(DateTimeInterface|string|null $date): ?DateTimeImmutable
    {
        if ($date === null) {
            return null;
        }

        if ($date instanceof DateTimeInterface) {
            return $this->truncateTime(DateTimeImmutable::createFromInterface($date));
        }

        try {
            return $this->truncateTime(new DateTimeImmutable($date));
        } catch (Exception) {
            throw new InvalidArgumentException("Date [{$date}] is not valid.");
        }
    }

    /**
     * Truncate the time to the prompt's precision.
     */
    protected function truncateTime(DateTimeImmutable $date): DateTimeImmutable
    {
        return $date->setTime(0, 0);
    }

    /**
     * Determine whether the given value is invalid when the prompt is required.
     */
    protected function isInvalidWhenRequired(mixed $value): bool
    {
        return $value === null;
    }
}
