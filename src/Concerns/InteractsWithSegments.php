<?php

namespace Laravel\Prompts\Concerns;

use Laravel\Prompts\Key;

trait InteractsWithSegments
{
    public string $focused;

    public string $segmentBuffer = '';

    protected bool $editingSegment = false;

    /**
     * Whether the last key filled a segment and moved focus on its own.
     */
    protected bool $autoAdvanced = false;

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
