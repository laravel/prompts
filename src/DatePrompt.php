<?php

namespace Laravel\Prompts;

use Closure;
use DateTimeImmutable;
use DateTimeInterface;
use Exception;
use InvalidArgumentException;
use RuntimeException;

class DatePrompt extends Prompt
{
    /**
     * The date currently highlighted on the calendar.
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

    /**
     * The digits typed into the date mask.
     */
    public string $buffer = '';

    /**
     * Create a new DatePrompt instance.
     */
    public function __construct(
        public string $label,
        DateTimeInterface|string|null $default = null,
        DateTimeInterface|string|null $min = null,
        DateTimeInterface|string|null $max = null,
        public bool|string $required = false,
        public mixed $validate = null,
        public string $hint = 'Use the arrow keys to navigate or type a date.',
        public ?Closure $transform = null,
        public int $weekStartsOn = 1,
    ) {
        if ($this->weekStartsOn < 0 || $this->weekStartsOn > 6) {
            throw new InvalidArgumentException('Argument [weekStartsOn] must be between 0 (Sunday) and 6 (Saturday).');
        }

        $this->default = $this->normalizeDate($default);
        $this->min = $this->normalizeDate($min);
        $this->max = $this->normalizeDate($max);

        if ($this->min !== null && $this->max !== null && $this->min > $this->max) {
            throw new InvalidArgumentException('Argument [min] must be on or before [max].');
        }

        $this->date = $this->clamp($this->default ?? new DateTimeImmutable('today'));

        $this->validate = $this->wrapValidation($this->validate);

        $this->on('key', fn ($key) => $this->handleKey($key));
    }

    /**
     * Handle a key press.
     */
    protected function handleKey(string $key): mixed
    {
        return match ($key) {
            Key::LEFT, Key::LEFT_ARROW, Key::CTRL_B => $this->goTo($this->date->modify('-1 day')),
            Key::RIGHT, Key::RIGHT_ARROW, Key::CTRL_F => $this->goTo($this->date->modify('+1 day')),
            Key::UP, Key::UP_ARROW, Key::CTRL_P => $this->goTo($this->date->modify('-7 days')),
            Key::DOWN, Key::DOWN_ARROW, Key::CTRL_N => $this->goTo($this->date->modify('+7 days')),
            Key::PAGE_UP => $this->goTo($this->addMonths(-1)),
            Key::PAGE_DOWN => $this->goTo($this->addMonths(1)),
            Key::SHIFT_UP => $this->goTo($this->addMonths(-12)),
            Key::SHIFT_DOWN => $this->goTo($this->addMonths(12)),
            Key::oneOf([Key::HOME, Key::CTRL_A], $key) => $this->goTo($this->date->modify('first day of this month')),
            Key::oneOf([Key::END, Key::CTRL_E], $key) => $this->goTo($this->date->modify('last day of this month')),
            Key::BACKSPACE, Key::CTRL_H => $this->buffer = substr($this->buffer, 0, -1),
            Key::ENTER => $this->submit(),
            default => $this->type($key),
        };
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
     * Get the selected date, or the typed digits over the mask, formatted for display.
     */
    public function formattedValue(): string
    {
        if ($this->buffer !== '') {
            $digits = str_pad($this->buffer, 8, '_');

            return sprintf('%s-%s-%s', substr($digits, 0, 4), substr($digits, 4, 2), substr($digits, 6, 2));
        }

        return $this->date->format('Y-m-d');
    }

    /**
     * Determine whether any moment of the given day of the highlighted month is selectable.
     */
    public function selectableDay(int $day): bool
    {
        $date = $this->date->setDate((int) $this->date->format('Y'), (int) $this->date->format('n'), $day);

        return ($this->min === null || $date->setTime(23, 59, 59) >= $this->min)
            && ($this->max === null || $date->setTime(0, 0) <= $this->max);
    }

    /**
     * Highlight the given date, keeping it within the min/max range.
     */
    protected function goTo(DateTimeImmutable $date): void
    {
        $this->buffer = '';
        $this->date = $this->clamp($date);
    }

    /**
     * Append the typed digits to the buffer and commit it once complete and valid.
     */
    protected function type(string $key): void
    {
        foreach (str_split($key) as $char) {
            if (ctype_digit($char) && strlen($this->buffer) < 8) {
                $this->buffer .= $char;
            }
        }

        $date = $this->bufferedDate();

        if ($date !== null && $date == $this->clamp($date)) {
            $this->date = $date;
            $this->buffer = '';
        }
    }

    /**
     * Get the date represented by a completely typed buffer.
     */
    protected function bufferedDate(): ?DateTimeImmutable
    {
        if (strlen($this->buffer) !== 8) {
            return null;
        }

        [$year, $month, $day] = [substr($this->buffer, 0, 4), substr($this->buffer, 4, 2), substr($this->buffer, 6, 2)];

        if (! checkdate((int) $month, (int) $day, (int) $year)) {
            return null;
        }

        return (new DateTimeImmutable("{$year}-{$month}-{$day}"))->setTime(0, 0);
    }

    /**
     * Wrap the validation logic to first verify the typed buffer.
     */
    protected function wrapValidation(mixed $validate): callable
    {
        return function ($value) use ($validate) {
            if (strlen($this->buffer) > 0 && strlen($this->buffer) < 8) {
                return 'Incomplete date.';
            }

            if (strlen($this->buffer) === 8) {
                return $this->bufferedDate() === null ? 'Invalid date.' : $this->rangeError($this->bufferedDate());
            }

            if (! $validate && ! isset(static::$validateUsing)) {
                return null;
            }

            return match (true) {
                is_callable($validate) => $validate($value),
                isset(static::$validateUsing) => (static::$validateUsing)($this),
                default => throw new RuntimeException('The validation logic is missing.'),
            };
        };
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
