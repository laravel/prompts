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

    public string $focused;

    public string $segmentBuffer = '';

    protected bool $editingSegment = false;

    public string $hint;

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
        ?string $hint = null,
        public ?Closure $transform = null,
        public int $weekStartsOn = 1,
        public bool $calendar = false,
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

        $this->focused = $calendar ? 'calendar' : 'year';
        $this->hint = $hint ?? ($calendar
            ? 'Use the arrow keys to navigate or type a date.'
            : 'Left/Right or Tab: move. Up/Down: change. Type to edit.');

        $this->validate = $this->wrapValidation($this->validate);

        $this->on('key', fn ($key) => $this->handleKey($key));
    }

    /**
     * Handle a key press.
     */
    protected function handleKey(string $key): mixed
    {
        if (! $this->calendar) {
            return $this->handleSegmentKey($key);
        }

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
        if (! $this->calendar) {
            return implode('-', $this->segmentValues());
        }

        if ($this->buffer !== '') {
            $digits = str_pad($this->buffer, 8, '_');

            return sprintf('%s-%s-%s', substr($digits, 0, 4), substr($digits, 4, 2), substr($digits, 6, 2));
        }

        return $this->date->format('Y-m-d');
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

    protected function segmentDisplay(string $segment, string $value): string
    {
        return $this->editingSegment && $this->focused === $segment
            ? str_pad($this->segmentBuffer, $segment === 'year' ? 4 : 2, '_')
            : $value;
    }

    protected function handleSegmentKey(string $key): mixed
    {
        match ($key) {
            Key::LEFT, Key::LEFT_ARROW, Key::CTRL_B => $this->moveFocus(-1),
            Key::RIGHT, Key::RIGHT_ARROW, Key::CTRL_F => $this->moveFocus(1),
            Key::TAB => $this->moveFocus(1, wrap: true),
            Key::SHIFT_TAB => $this->moveFocus(-1, wrap: true),
            Key::UP, Key::UP_ARROW, Key::CTRL_P => $this->stepSegment(1),
            Key::DOWN, Key::DOWN_ARROW, Key::CTRL_N => $this->stepSegment(-1),
            Key::BACKSPACE, Key::CTRL_H => $this->backspaceSegment(),
            Key::ENTER => $this->submit(),
            default => $this->typeIntoSegment($key),
        };

        return null;
    }

    /** @return list<string> */
    protected function segments(): array
    {
        return ['year', 'month', 'day'];
    }

    protected function moveFocus(int $direction, bool $wrap = false): bool
    {
        if (! $this->commitSegment()) {
            return false;
        }

        $segments = $this->segments();
        $index = array_search($this->focused, $segments) + $direction;
        $count = count($segments);
        $index = $wrap ? ($index + $count) % $count : max(0, min($count - 1, $index));

        if ($this->focused === $segments[$index]) {
            return false;
        }

        $this->focused = $segments[$index];

        return true;
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

    protected function typeIntoSegment(string $key): void
    {
        if ($key !== '' && $key[0] === "\e") {
            return;
        }

        foreach (str_split($key) as $char) {
            if (in_array($char, ['-', ':', ' '])) {
                if (! $this->moveFocus(1)) {
                    return;
                }

                continue;
            }

            if (! ctype_digit($char)) {
                continue;
            }

            $this->editingSegment = true;
            $width = $this->focused === 'year' ? 4 : 2;

            if (strlen($this->segmentBuffer) >= $width) {
                $this->segmentBuffer = '';
            }

            $this->segmentBuffer .= $char;
        }
    }

    protected function backspaceSegment(): void
    {
        if (! $this->editingSegment) {
            $this->segmentBuffer = $this->segmentValues()[$this->focused];
            $this->editingSegment = true;
        }

        $this->segmentBuffer = substr($this->segmentBuffer, 0, -1);
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

    protected function commitSegment(): bool
    {
        if (! $this->editingSegment) {
            return true;
        }

        if (($error = $this->segmentError()) !== null) {
            $this->state = 'error';
            $this->error = $error;

            return false;
        }

        $this->date = $this->segmentDate();
        $this->segmentBuffer = '';
        $this->editingSegment = false;

        return true;
    }

    protected function submit(): void
    {
        if ($this->commitSegment()) {
            if ($this->calendar) {
                $this->type('');
            }

            parent::submit();
        }
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
        if ($key !== '' && $key[0] === "\e") {
            return;
        }

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

        return $this->date->setDate((int) $year, (int) $month, (int) $day)->setTime(0, 0);
    }

    /**
     * Wrap the validation logic to first verify the typed buffer and the range.
     */
    protected function wrapValidation(mixed $validate): callable
    {
        return function ($value) use ($validate) {
            if (($error = $this->segmentError()) !== null) {
                return $error;
            }

            if (strlen($this->buffer) > 0 && strlen($this->buffer) < 8) {
                return 'Incomplete date.';
            }

            if (strlen($this->buffer) === 8) {
                return $this->bufferedDate() === null ? 'Invalid date.' : $this->rangeError($this->bufferedDate());
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
