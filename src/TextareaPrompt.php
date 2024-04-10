<?php

namespace Laravel\Prompts;

class TextareaPrompt extends Prompt
{
    use Concerns\Scrolling;
    use Concerns\Truncation;
    use Concerns\TypedValue;

    /**
     * The width of the textarea.
     */
    public int $width = 60;

    /**
     * Create a new TextareaPrompt instance.
     */
    public function __construct(
        public string $label,
        public string $placeholder = '',
        public string $default = '',
        public bool|string $required = false,
        public mixed $validate = null,
        public string $hint = '',
        int $rows = 5,
    ) {
        $this->scroll = $rows;

        $this->initializeScrolling();

        $this->trackTypedValue(
            default: $default,
            submit: false,
            allowNewLine: true,
        );

        $this->on('key', function ($key) {
            if ($key[0] === "\e") {
                match ($key) {
                    Key::UP, Key::UP_ARROW, Key::CTRL_P => $this->handleUpKey(),
                    Key::DOWN, Key::DOWN_ARROW, Key::CTRL_N => $this->handleDownKey(),
                    default => null,
                };

                return;
            }

            // Keys may be buffered.
            foreach (mb_str_split($key) as $key) {
                if ($key === Key::CTRL_D) {
                    $this->submit();

                    return;
                }
            }
        });
    }

    /**
     * Get the formatted value with a virtual cursor.
     */
    public function valueWithCursor(): string
    {
        if ($this->value() === '') {
            return $this->wrappedPlaceholderWithCursor();
        }

        return $this->addCursor($this->wrappedValue(), $this->cursorPosition + $this->cursorOffset(), -1);
    }

    /**
     * The word-wrapped version of the typed value.
     */
    public function wrappedValue(): string
    {
        return $this->mbWordwrap($this->value(), $this->width, PHP_EOL, true);
    }

    /**
     * The formatted lines.
     *
     * @return array<int, string>
     */
    public function lines(): array
    {
        return explode(PHP_EOL, $this->wrappedValue());
    }

    /**
     * The currently visible lines.
     *
     * @return array<int, string>
     */
    public function visible(): array
    {
        $this->adjustVisibleWindow();

        $withCursor = $this->valueWithCursor();

        return array_slice(explode(PHP_EOL, $withCursor), $this->firstVisible, $this->scroll, preserve_keys: true);
    }

    /**
     * Handle the up key press.
     */
    protected function handleUpKey(): void
    {
        if ($this->cursorPosition === 0) {
            return;
        }

        $lines = collect($this->lines());

        // Line length + 1 for the newline character
        $lineLengths = $lines->map(fn ($line, $index) => mb_strwidth($line) + ($index === $lines->count() - 1 ? 0 : 1));

        $currentLineIndex = $this->currentLineIndex();

        if ($currentLineIndex === 0) {
            // They're already at the first line, jump them to the first position
            $this->cursorPosition = 0;

            return;
        }

        $currentLines = $lineLengths->slice(0, $currentLineIndex + 1);

        $currentColumn = $currentLines->last() - ($currentLines->sum() - $this->cursorPosition);

        $destinationLineLength = ($lineLengths->get($currentLineIndex - 1) ?? $currentLines->first()) - 1;

        $newColumn = min($destinationLineLength, $currentColumn);

        $fullLines = $currentLines->slice(0, -2);

        $this->cursorPosition = $fullLines->sum() + $newColumn;
    }

    /**
     * Handle the down key press.
     */
    protected function handleDownKey(): void
    {
        $lines = collect($this->lines());

        // Line length + 1 for the newline character
        $lineLengths = $lines->map(fn ($line, $index) => mb_strwidth($line) + ($index === $lines->count() - 1 ? 0 : 1));

        $currentLineIndex = $this->currentLineIndex();

        if ($currentLineIndex === $lines->count() - 1) {
            // They're already at the last line, jump them to the last position
            $this->cursorPosition = mb_strwidth($lines->implode(PHP_EOL));

            return;
        }

        // Lines up to and including the current line
        $currentLines = $lineLengths->slice(0, $currentLineIndex + 1);

        $currentColumn = $currentLines->last() - ($currentLines->sum() - $this->cursorPosition);

        $destinationLineLength = $lineLengths->get($currentLineIndex + 1) ?? $currentLines->last();

        if ($currentLineIndex + 1 !== $lines->count() - 1) {
            $destinationLineLength--;
        }

        $newColumn = min(max(0, $destinationLineLength), $currentColumn);

        $this->cursorPosition = $currentLines->sum() + $newColumn;
    }

    /**
     * Adjust the visible window to ensure the cursor is always visible.
     */
    protected function adjustVisibleWindow(): void
    {
        if (count($this->lines()) < $this->scroll) {
            return;
        }

        $currentLineIndex = $this->currentLineIndex();

        while ($this->firstVisible + $this->scroll <= $currentLineIndex) {
            $this->firstVisible++;
        }

        if ($currentLineIndex === $this->firstVisible - 1) {
            $this->firstVisible = max(0, $this->firstVisible - 1);
        }

        // Make sure there are always the scroll amount visible
        if ($this->firstVisible + $this->scroll > count($this->lines())) {
            $this->firstVisible = count($this->lines()) - $this->scroll;
        }
    }

    /**
     * Get the index of the current line that the cursor is on.
     */
    protected function currentLineIndex(): int
    {
        $totalLineLength = 0;

        return (int) collect($this->lines())->search(function ($line) use (&$totalLineLength) {
            $totalLineLength += mb_strwidth($line) + 1;

            return $totalLineLength > $this->cursorPosition;
        }) ?: 0;
    }

    /**
     * Calculate the cursor offset considering wrapped words.
     */
    protected function cursorOffset(): int
    {
        $cursorOffset = 0;

        preg_match_all('/\S{'.$this->width.',}/u', $this->value(), $matches, PREG_OFFSET_CAPTURE);

        foreach ($matches[0] as $match) {
            if ($this->cursorPosition + $cursorOffset >= $match[1] + mb_strwidth($match[0])) {
                $cursorOffset += (int) floor(mb_strwidth($match[0]) / $this->width);
            }
        }

        return $cursorOffset;
    }

    /**
     * A wrapped version of the placeholder with the virtual cursor.
     */
    protected function wrappedPlaceholderWithCursor(): string
    {
        return implode(PHP_EOL, array_map(
            $this->dim(...),
            explode(PHP_EOL, $this->addCursor(
                $this->mbWordwrap($this->placeholder, $this->width, PHP_EOL, true),
                cursorPosition: 0,
            ))
        ));
    }
}
