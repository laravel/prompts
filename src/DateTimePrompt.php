<?php

namespace Laravel\Prompts;

use Closure;
use DateTimeImmutable;
use DateTimeInterface;

class DateTimePrompt extends DatePrompt
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
     * The segment that currently has focus: "calendar", "hour", "minute", or "second".
     */
    public string $focused = 'calendar';

    /**
     * Create a new DateTimePrompt instance.
     */
    public function __construct(
        string $label,
        DateTimeInterface|string|null $default = null,
        DateTimeInterface|string|null $min = null,
        DateTimeInterface|string|null $max = null,
        bool|string $required = false,
        mixed $validate = null,
        string $hint = 'Use the arrow keys to navigate or type a date. Tab edits the time.',
        ?Closure $transform = null,
        int $weekStartsOn = 1,
        public bool $withSeconds = false,
    ) {
        parent::__construct($label, $default, $min, $max, $required, $validate, $hint, $transform, $weekStartsOn);

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
        return match (true) {
            $key === Key::TAB => $this->moveFocus(1),
            $key === Key::SHIFT_TAB => $this->moveFocus(-1),
            $this->focused === 'calendar' => parent::handleKey($key),
            default => $this->handleTimeKey($key),
        };
    }

    /**
     * Handle a key press while a time segment has focus.
     */
    protected function handleTimeKey(string $key): mixed
    {
        return match ($key) {
            Key::UP, Key::UP_ARROW, Key::CTRL_P => $this->stepSegment(1),
            Key::DOWN, Key::DOWN_ARROW, Key::CTRL_N => $this->stepSegment(-1),
            Key::LEFT, Key::LEFT_ARROW, Key::CTRL_B => $this->moveFocus(-1),
            Key::RIGHT, Key::RIGHT_ARROW, Key::CTRL_F => $this->focused === $this->lastSegment() ? null : $this->moveFocus(1),
            Key::ENTER => $this->submit(),
            default => $this->typeIntoSegment($key),
        };
    }

    /**
     * Move the focus by the given number of segments, wrapping around.
     */
    protected function moveFocus(int $direction): void
    {
        $segments = $this->segments();

        $index = array_search($this->focused, $segments) + $direction;

        $this->focused = $segments[($index + count($segments)) % count($segments)];
    }

    /**
     * Step the focused time segment, wrapping around.
     */
    protected function stepSegment(int $step): void
    {
        match ($this->focused) {
            'hour' => $this->hour = ($this->hour + $step + 24) % 24,
            'minute' => $this->minute = ($this->minute + $step + 60) % 60,
            'second' => $this->second = ($this->second + $step + 60) % 60,
            default => null,
        };
    }

    /**
     * Type digits into the focused time segment, rolling the last two digits.
     */
    protected function typeIntoSegment(string $key): void
    {
        if ($key !== '' && $key[0] === "\e") {
            return;
        }

        foreach (str_split($key) as $char) {
            if (! ctype_digit($char)) {
                continue;
            }

            match ($this->focused) {
                'hour' => $this->hour = $this->rollSegment($this->hour, (int) $char, 23),
                'minute' => $this->minute = $this->rollSegment($this->minute, (int) $char, 59),
                'second' => $this->second = $this->rollSegment($this->second, (int) $char, 59),
                default => null,
            };
        }
    }

    /**
     * Roll the typed digit into the segment, restarting from the digit on overflow.
     */
    protected function rollSegment(int $current, int $digit, int $max): int
    {
        $rolled = ($current % 10) * 10 + $digit;

        return $rolled <= $max ? $rolled : $digit;
    }

    /**
     * The focusable segments.
     *
     * @return list<string>
     */
    protected function segments(): array
    {
        return $this->withSeconds
            ? ['calendar', 'hour', 'minute', 'second']
            : ['calendar', 'hour', 'minute'];
    }

    /**
     * The last focusable segment.
     */
    protected function lastSegment(): string
    {
        $segments = $this->segments();

        return $segments[count($segments) - 1];
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
            ? $date
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
