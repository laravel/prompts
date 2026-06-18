<?php

namespace Laravel\Prompts\Themes\Default;

use InvalidArgumentException;
use Laravel\Prompts\Elements\BulletedList;
use Laravel\Prompts\Elements\Contract as ElementContract;
use Laravel\Prompts\Elements\Heading;
use Laravel\Prompts\Elements\NumberedList;
use Laravel\Prompts\Callout;
use Laravel\Prompts\Themes\Default\Concerns\InteractsWithStrings;

class CalloutRenderer extends Renderer
{
    use Concerns\DrawsBoxes;
    use InteractsWithStrings;

    /**
     * Render the text prompt.
     */
    public function __invoke(Callout $prompt): string
    {
        $content = is_array($prompt->content) ? $prompt->content : [$prompt->content];

        $sections = [];

        foreach ($content as $part) {
            $result = $this->resolvePart($part);

            if (is_array($result)) {
                $sections[] = implode(PHP_EOL, $result);
            } else {
                $sections[] = implode(PHP_EOL, $this->ansiWordwrap($result, $this->minWidth));
            }
        }

        $message = implode(PHP_EOL . PHP_EOL, $sections);

        return match ($prompt->type) {
            'error' => $this
                ->box(
                    $this->red($this->truncate('⚠ ' . $prompt->label, $prompt->terminal()->cols() - 6)),
                    $message,
                    color: 'red',
                ),

            'warning' => $this
                ->box(
                    $this->yellow($this->truncate('⚠ ' . $prompt->label, $prompt->terminal()->cols() - 6)),
                    $message,
                    color: 'yellow',
                ),

            default => $this
                ->box(
                    $this->cyan($this->truncate($prompt->label, $prompt->terminal()->cols() - 6)),
                    $message,
                ),
        };
    }

    protected function resolvePart(string|ElementContract $part)
    {
        if (is_string($part)) {
            return $part;
        }

        if (!$part instanceof ElementContract) {
            throw new InvalidArgumentException('Unknown argument type sent to ' . self::class);
        }

        if ($part instanceof Heading) {
            return $this->bold(implode('', $part->content()));
        }

        if ($part instanceof BulletedList) {
            return array_map(function ($p) {
                $lines = $this->ansiWordwrap($p, $this->minWidth - 2);
                $finalLines = [];

                foreach ($lines as $index => $line) {
                    if ($index === 0) {
                        $finalLines[] = $this->dim('·') . ' ' . $line;
                    } else {
                        $finalLines[] = '  ' . $line;
                    }
                }

                return implode(PHP_EOL, $finalLines);
            }, $part->content());
        }

        if ($part instanceof NumberedList) {
            $finalLines = [];

            foreach ($part->content() as $i => $p) {
                // +1 for "."
                $widestNumber = mb_strwidth(count($part->content())) + 1;
                $partLines = [];
                // -1 for ' ' after number
                $lines = $this->ansiWordwrap($p, $this->minWidth - $widestNumber - 1);

                foreach ($lines as $index => $line) {
                    if ($index === 0) {
                        $partLines[] = $this->dim(mb_str_pad(($i + 1) . '.', $widestNumber, pad_type: STR_PAD_LEFT)) . ' ' . $line;
                    } else {
                        // +1 for ' ' after number
                        $partLines[] = str_repeat(' ', $widestNumber + 1) . $line;
                    }
                }

                $finalLines[] = implode(PHP_EOL, $partLines);
            }

            return $finalLines;
        }

        return implode(PHP_EOL, $part->content());
    }
}
