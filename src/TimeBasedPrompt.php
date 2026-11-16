<?php

namespace Laravel\Prompts;

use Closure;
use DateTimeImmutable;
use DateTimeInterface;
use Exception;
use InvalidArgumentException;
use RuntimeException;

abstract class TimeBasedPrompt extends Prompt
{
    /**
     * The selected value.
     */
    public DateTimeImmutable $date;

    /**
     * The default value.
     */
    public ?DateTimeImmutable $default;

    /**
     * The earliest selectable value.
     */
    public ?DateTimeImmutable $min;

    /**
     * The latest selectable value.
     */
    public ?DateTimeImmutable $max;

    public string $hint;

    /**
     * What the prompt's values are called in argument errors.
     */
    protected string $noun = 'Date';

    public string $focused;

    public string $segmentBuffer = '';

    protected bool $editingSegment = false;

    /**
     * Whether the last key filled a segment and moved focus on its own.
     */
    protected bool $autoAdvanced = false;

    /**
     * Create a new TimeBasedPrompt instance.
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

        if ($this->min !== null && $this->max !== null && $this->compare($this->min, $this->max) > 0) {
            throw new InvalidArgumentException('Argument [min] must be on or before [max].');
        }

        $this->goTo($this->default ?? $this->initialDate());

        $this->focused = $this->segments()[0];
        $this->hint = $hint ?? 'Left/Right or Tab: move. Up/Down: change. Type to edit.';

        $this->validate = $this->wrapValidation($this->validate);

        $this->on('key', fn ($key) => $this->handleSegmentKey($key));
    }

    /**
     * Get the display value of each segment, keyed by segment name.
     *
     * @return array<string, string>
     */
    abstract public function segmentValues(): array;

    /**
     * Join the given segment values for display.
     *
     * @param  array<string, string>  $segments
     */
    abstract public function formatSegments(array $segments): string;

    /**
     * The focusable segments, in order.
     *
     * @return list<string>
     */
    abstract protected function segments(): array;

    /**
     * Step the focused segment up or down.
     */
    abstract protected function stepSegment(int $step): void;

    /**
     * Build the value with the edited segment applied, or null when it is invalid.
     */
    abstract protected function segmentDate(): ?DateTimeImmutable;

    /**
     * The value to start on when there is no default.
     */
    abstract protected function initialDate(): DateTimeImmutable;

    /**
     * Truncate the time to the prompt's precision.
     */
    abstract protected function truncateTime(DateTimeImmutable $date): DateTimeImmutable;

    /**
     * The format used when displaying values in messages.
     */
    abstract protected function displayFormat(): string;

    /**
     * Get the selected value.
     */
    public function value(): ?DateTimeImmutable
    {
        if (static::$interactive === false) {
            return $this->default;
        }

        return $this->date;
    }

    /**
     * Get the selected value formatted for display.
     */
    public function formattedValue(): string
    {
        return $this->formatSegments($this->segmentValues());
    }

    /**
     * Compare two values at the prompt's precision.
     */
    protected function compare(DateTimeImmutable $a, DateTimeImmutable $b): int
    {
        return $a <=> $b;
    }

    /**
     * Get the error for the segment being edited, if any.
     */
    protected function segmentError(): ?string
    {
        if (! $this->editingSegment) {
            return null;
        }

        if ($this->segmentBuffer === '' || ($this->focused === 'year' && strlen($this->segmentBuffer) < 4)) {
            return "Incomplete {$this->focused}.";
        }

        if ($this->segmentDate() !== null) {
            return null;
        }

        return in_array($this->focused, ['year', 'month', 'day']) ? 'Invalid date.' : 'Invalid time.';
    }

    protected function submit(): void
    {
        if ($this->commitSegment()) {
            parent::submit();
        }
    }

    /**
     * Move to the given value, keeping it within the min/max range.
     */
    protected function goTo(DateTimeImmutable $date): void
    {
        $this->date = $this->clamp($date);
    }

    /**
     * Constrain the given value to the min/max range.
     */
    protected function clamp(DateTimeImmutable $date): DateTimeImmutable
    {
        return match (true) {
            $this->min !== null && $this->compare($date, $this->min) < 0 => $this->min,
            $this->max !== null && $this->compare($date, $this->max) > 0 => $this->max,
            default => $date,
        };
    }

    /**
     * Get the validation error for a value outside of the min/max range.
     */
    protected function rangeError(DateTimeImmutable $date): ?string
    {
        return match (true) {
            $this->min !== null && $this->compare($date, $this->min) < 0 => 'Must be on or after '.$this->min->format($this->displayFormat()).'.',
            $this->max !== null && $this->compare($date, $this->max) > 0 => 'Must be on or before '.$this->max->format($this->displayFormat()).'.',
            default => null,
        };
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
     * Normalize the given value to a DateTimeImmutable at the prompt's precision.
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
            $parsed = new DateTimeImmutable($date);
            $errors = DateTimeImmutable::getLastErrors();

            // PHP rolls impossible dates like Feb 30 forward with only a warning.
            if ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0)) {
                throw new InvalidArgumentException;
            }

            return $this->truncateTime($parsed);
        } catch (Exception) {
            throw new InvalidArgumentException("{$this->noun} [{$date}] is not valid.");
        }
    }

    /**
     * Determine whether the given value is invalid when the prompt is required.
     */
    protected function isInvalidWhenRequired(mixed $value): bool
    {
        return $value === null;
    }

    protected function segmentDisplay(string $segment, string $value): string
    {
        return $this->editingSegment && $this->focused === $segment
            ? str_pad($this->segmentBuffer, $segment === 'year' ? 4 : 2, '_')
            : $value;
    }

    protected function handleSegmentKey(string $key): mixed
    {
        $autoAdvanced = $this->autoAdvanced;
        $this->autoAdvanced = false;

        // Focus already moved when the segment filled, so a habitual Tab or Right shouldn't skip the next one.
        if ($autoAdvanced && in_array($key, [Key::TAB, Key::RIGHT, Key::RIGHT_ARROW, Key::CTRL_F])) {
            return null;
        }

        match ($key) {
            Key::LEFT, Key::LEFT_ARROW, Key::CTRL_B => $this->moveFocus(-1),
            Key::RIGHT, Key::RIGHT_ARROW, Key::CTRL_F => $this->moveFocus(1),
            Key::TAB => $this->moveFocus(1, wrap: true),
            Key::SHIFT_TAB => $this->moveFocus(-1, wrap: true),
            Key::UP, Key::UP_ARROW, Key::CTRL_P => $this->stepSegment(1),
            Key::DOWN, Key::DOWN_ARROW, Key::CTRL_N => $this->stepSegment(-1),
            Key::BACKSPACE, Key::CTRL_H => $this->backspaceSegment(),
            Key::ENTER => $this->submit(),
            default => $this->typeIntoSegment($key, $autoAdvanced),
        };

        return null;
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

    protected function typeIntoSegment(string $key, bool $autoAdvanced = false): void
    {
        if ($key !== '' && $key[0] === "\e") {
            return;
        }

        foreach (str_split($key) as $char) {
            $skipSeparator = $autoAdvanced;
            $autoAdvanced = false;

            if ($char === ' ' && $this->focused === 'period' && ! $this->editingSegment) {
                continue;
            }

            if (in_array($char, ['-', ':', ' '])) {
                if ($skipSeparator) {
                    continue;
                }

                if (! $this->moveFocus(1)) {
                    return;
                }

                continue;
            }

            if (($input = $this->segmentInput($char)) === null) {
                continue;
            }

            $this->editingSegment = true;
            $width = $this->focused === 'year' ? 4 : 2;

            if (strlen($input) === $width || strlen($this->segmentBuffer) >= $width) {
                $this->segmentBuffer = '';
            }

            $this->segmentBuffer .= $input;

            if (strlen($this->segmentBuffer) === $width) {
                if (! $this->moveFocus(1)) {
                    return;
                }

                $autoAdvanced = true;
            }
        }

        $this->autoAdvanced = $autoAdvanced;
    }

    protected function segmentInput(string $char): ?string
    {
        return ctype_digit($char) ? $char : null;
    }

    protected function backspaceSegment(): void
    {
        if (! $this->editingSegment) {
            $this->segmentBuffer = $this->segmentValues()[$this->focused];
            $this->editingSegment = true;
        }

        $this->segmentBuffer = substr($this->segmentBuffer, 0, -1);
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
}
