<?php

namespace Laravel\Prompts;

use Closure;

class AutoCompletePrompt extends Prompt
{
    use Concerns\TypedValue;

    protected string $match = '';

    /**
     * Create a new TextPrompt instance.
     */
    public function __construct(
        public string $label,
        public string $placeholder = '',
        public string $default = '',
        public bool|string $required = false,
        public mixed $validate = null,
        public string $hint = '',
        public ?Closure $transform = null,
        public array $options = [],
    ) {
        $this->trackTypedValue($default);

        $this->on('key', function ($key) {
            if (in_array($key, [Key::TAB, Key::RIGHT, Key::RIGHT_ARROW, Key::OPTION_BACKSPACE])) {
                $match = $this->getMatch();

                if ($match !== '') {
                    $this->typedValue = $match;
                    $this->cursorPosition = mb_strlen($match);
                }
            }
        });
    }

    /**
     * Get the entered value with a virtual cursor.
     */
    public function valueWithCursor(int $maxWidth): string
    {
        if ($this->value() === '') {
            return $this->dim($this->addCursor($this->placeholder, 0, $maxWidth));
        }

        $match = $this->match = $this->getMatch();

        if ($this->match !== '') {
            if ($this->cursorPosition > strlen($this->value())) {
                $match = substr($this->match, strlen($this->value()));
            } else {
                $match = substr($this->match, strlen($this->value()) + 1);
            }
        }

        return $this->addCursor(
            $this->value(),
            $this->cursorPosition,
            $maxWidth
        ) . $this->dim($match);
    }

    /**
     * Add a virtual cursor to the value and truncate if necessary.
     */
    protected function addCursor(string $value, int $cursorPosition, ?int $maxWidth = null): string
    {
        $before = mb_substr($value, 0, $cursorPosition);
        $current = mb_substr($value, $cursorPosition, 1);
        $after = mb_substr($value, $cursorPosition + 1);

        $cursor = mb_strlen($current) && $current !== PHP_EOL ? $current : ' ';

        if ($value !== $this->match && $this->match !== '' && $cursor === ' ') {
            $cursor = substr($this->match, $cursorPosition, 1);
        }

        $spaceBefore = $maxWidth < 0 || $maxWidth === null ? mb_strwidth($before) : $maxWidth - mb_strwidth($cursor) - (mb_strwidth($after) > 0 ? 1 : 0);
        [$truncatedBefore, $wasTruncatedBefore] = mb_strwidth($before) > $spaceBefore
            ? [$this->trimWidthBackwards($before, 0, $spaceBefore - 1), true]
            : [$before, false];

        $spaceAfter = $maxWidth < 0 || $maxWidth === null ? mb_strwidth($after) : $maxWidth - ($wasTruncatedBefore ? 1 : 0) - mb_strwidth($truncatedBefore) - mb_strwidth($cursor);
        [$truncatedAfter, $wasTruncatedAfter] = mb_strwidth($after) > $spaceAfter
            ? [mb_strimwidth($after, 0, $spaceAfter - 1), true]
            : [$after, false];

        return ($wasTruncatedBefore ? $this->dim('…') : '')
            . $truncatedBefore
            . $this->inverse($cursor)
            . ($current === PHP_EOL ? PHP_EOL : '')
            . $truncatedAfter
            . ($wasTruncatedAfter ? $this->dim('…') : '');
    }

    protected function getMatch(): string
    {
        return collect($this->options)->first(
            fn($option) => str_starts_with(
                strtolower($option),
                strtolower($this->value()),
            ),
        ) ?? '';
    }
}
