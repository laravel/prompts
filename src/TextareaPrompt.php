<?php

namespace Laravel\Prompts;

use Closure;

class TextareaPrompt extends Prompt
{
    use Concerns\TypedValue;
    use Concerns\ReducesScrollingToFitTerminal;

    /**
     * The index of the first visible option.
     */
    public int $firstVisible = 0;

    public int $scroll = 5;

    /**
     * Create a new TextareaPrompt instance.
     */
    public function __construct(
        public string $label,
        public string $placeholder = '',
        public string $default = '',
        public bool|string $required = false,
        public ?Closure $validate = null,
        public string $hint = ''
    ) {
        $this->trackTypedValue(
            default: $default,
            submit: false,
            ignore: fn ($key) => $key === Key::ENTER,
        );

        $this->reduceScrollingToFitTerminal();

        $this->cursorPosition = 0;

        $this->on(
            'key',
            function ($key) {
                if ($key === Key::ENTER) {
                    $this->typedValue = mb_substr($this->typedValue, 0, $this->cursorPosition) . $key . mb_substr($this->typedValue, $this->cursorPosition);
                    $this->cursorPosition++;
                }

                if ($key[0] === "\e") {
                    match ($key) {
                        Key::UP, Key::UP_ARROW, Key::CTRL_P => $this->handleUpKey(),
                        Key::DOWN, Key::DOWN_ARROW, Key::CTRL_N => $this->handleDownKey(),
                        Key::DELETE => $this->checkScrollPosition(),
                        default => null,
                    };

                    return;
                }

                // Keys may be buffered.
                foreach (mb_str_split($key) as $key) {
                    if ($key === Key::CTRL_D) {
                        $this->submit();
                    }
                }
            }
        );
    }

    protected function checkScrollPosition()
    {
        $totalLineLength = 0;

        $currentLineIndex = collect($this->lines())->search(function ($line) use (&$totalLineLength) {
            $totalLineLength += mb_strlen($line);

            return $totalLineLength >= $this->cursorPosition;
        });

        ray($this->firstVisible + $this->scroll, $currentLineIndex,);

        if ($this->firstVisible + $this->scroll <= $currentLineIndex) {
            $this->firstVisible++;
        }


        // if ($currentLineIndex < $this->firstVisible) {
        //     $this->firstVisible--;
        // }

        // if ($currentLineIndex > $this->firstVisible + $this->scroll) {
        //     $this->firstVisible++;
        // }





        // if ($this->firstVisible + $this->scroll < count($this->lines())) {
        //     ray('adding');
        //     $this->firstVisible++;
        // }

        // if ($this->firstVisible - $this->scroll > count($this->lines())) {
        //     ray('subtracting');
        //     $this->firstVisible--;
        // }
    }

    protected function handleUpKey(): void
    {
        if ($this->cursorPosition === 0) {
            return;
        }

        $lines = collect($this->lines());

        // Line length + 1 for the newline character
        $lineLengths = $lines->map(fn ($line, $index) => mb_strlen($line) + ($index === $lines->count() - 1 ? 0 : 1));

        $totalLineLength = 0;

        $currentLineIndex = $lineLengths->search(function ($lineLength) use (&$totalLineLength) {
            $totalLineLength += $lineLength;

            return $totalLineLength >= $this->cursorPosition;
        });

        if ($currentLineIndex === 0) {
            // They're already at the first line, jump them to the first position
            $this->cursorPosition = 0;
            return;
        }

        if ($currentLineIndex + $this->firstVisible < $this->scroll && $this->firstVisible > 0) {
            $this->firstVisible--;
        }

        $currentLines = $lineLengths->slice(0, $currentLineIndex + 1);

        $currentColumn = $currentLines->last() - ($currentLines->sum() - $this->cursorPosition);

        $destinationLineLength = $lineLengths->get($currentLineIndex - 1) ?? $currentLines->first();

        $newColumn = min($destinationLineLength, $currentColumn);

        // ray($lineLengths->get($currentLineIndex - 1), compact(
        //     'currentLineIndex',
        //     'currentColumn',
        //     'destinationLineLength',
        //     'newColumn',
        //     'currentLines',
        //     'lineLengths',
        //     'lines',
        //     'totalLineLength'
        // ));

        if ($newColumn < $currentColumn) {
            $newColumn--;
        }

        $fullLines = $currentLines->slice(0, -2);

        $this->cursorPosition = $fullLines->sum() + $newColumn;
    }

    protected function handleDownKey(): void
    {
        $lines = collect($this->lines());

        // $this->firstVisible = min($lines->count() - $this->scroll, $this->firstVisible + 1);

        // if ($this->cursorPosition === mb_strlen($lines->implode(PHP_EOL))) {
        //     return;
        // }

        // Line length + 1 for the newline character
        $lineLengths = $lines->map(fn ($line, $index) => mb_strlen($line) + ($index === $lines->count() - 1 ? 0 : 1));

        $totalLineLengths = 0;

        $currentLineIndex = $lineLengths->search(function ($lineLength) use (&$totalLineLengths) {
            $totalLineLengths += $lineLength;

            return $totalLineLengths >= $this->cursorPosition;
        });


        if ($currentLineIndex === $lines->count() - 1) {
            // They're already at the last line, jump them to the last position
            // TODO: Fix this number, it's not using $lines
            $this->cursorPosition = mb_strlen($this->typedValue);
            return;
        }

        if ($currentLineIndex + 1 >= $this->firstVisible + $this->scroll) {
            $this->firstVisible++;
        }

        $currentLines = $lineLengths->slice(0, $currentLineIndex + 1);

        $currentColumn = $currentLines->last() - ($currentLines->sum() - $this->cursorPosition);

        $newLineLength = $lineLengths->get($currentLineIndex + 1) ?? $currentLines->last();

        $newColumn = min($newLineLength, $currentColumn);

        $fullLines = $lineLengths->slice(0, $currentLines->count());

        $this->cursorPosition = $fullLines->sum() + $newColumn;
    }

    /**
     * The currently visible options.
     *
     * @return array<int|string, string>
     */
    public function visible(): array
    {
        $currentLineIndex = $this->currentLineIndex();

        if ($this->firstVisible + $this->scroll <= $currentLineIndex) {
            $this->firstVisible++;
        }

        // Make sure there are always the scroll amount visible
        if ($this->firstVisible + $this->scroll > count($this->lines())) {
            $this->firstVisible = count($this->lines()) - $this->scroll;
        }

        return array_slice($this->lines(), $this->firstVisible, $this->scroll, preserve_keys: true);
    }

    protected function currentLineIndex(): int
    {
        $totalLineLength = 0;

        return collect($this->lines())->search(function ($line) use (&$totalLineLength) {
            $totalLineLength += mb_strlen($line);

            return $totalLineLength >= $this->cursorPosition;
        });
    }

    public function lines(): array
    {
        $value = $this->valueWithCursor(10_000);

        $lines = explode(PHP_EOL, $value);

        while (count($lines) < $this->scroll) {
            $lines[] = '';
        }

        return $lines;
    }

    /**
     * Get the entered value with a virtual cursor.
     */
    public function valueWithCursor(int $maxWidth): string
    {
        if ($this->value() === '') {
            return $this->dim($this->addCursor($this->placeholder, 0, $maxWidth));
        }

        // TODO: Figure out the real number here, this comes from the renderer?
        $value = wordwrap($this->value(), 59, PHP_EOL, true);
        // TODO: Deal with max width properly
        return $this->addCursor($value, $this->cursorPosition, $maxWidth);
    }
}
