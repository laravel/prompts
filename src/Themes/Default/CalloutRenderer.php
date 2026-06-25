<?php

namespace Laravel\Prompts\Themes\Default;

use Laravel\Prompts\Callout;
use Laravel\Prompts\Elements\BulletedList;
use Laravel\Prompts\Elements\ElementContract;
use Laravel\Prompts\Elements\Heading;
use Laravel\Prompts\Elements\KeyValueList;
use Laravel\Prompts\Elements\Link;
use Laravel\Prompts\Elements\NumberedList;

class CalloutRenderer extends Renderer
{
    use Concerns\DrawsBoxes;

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

        $message = implode(PHP_EOL.PHP_EOL, $sections);

        return match ($prompt->type) {
            'error' => $this
                ->box(
                    $this->red($this->truncate('⚠ '.$prompt->label, $prompt->terminal()->cols() - 6)),
                    $message,
                    color: 'red',
                    info: $prompt->info,
                ),

            'warning' => $this
                ->box(
                    $this->yellow($this->truncate('⚠ '.$prompt->label, $prompt->terminal()->cols() - 6)),
                    $message,
                    color: 'yellow',
                    info: $prompt->info,
                ),

            default => $this
                ->box(
                    $this->cyan($this->truncate($prompt->label, $prompt->terminal()->cols() - 6)),
                    $message,
                    info: $prompt->info,
                ),
        };
    }

    /**
     * Resolve a part of the callout content into a string or array of strings.
     */
    /**
     * @return string|array<int, string>
     */
    protected function resolvePart(string|ElementContract $part): string|array
    {
        if (is_string($part)) {
            return $this->autoFormat($part);
        }

        if ($part instanceof Heading) {
            return $this->bold(
                $this->autoFormat(
                    implode('', $part->content())
                ),
            );
        }

        if ($part instanceof BulletedList) {
            $finalLines = [];

            foreach ($part->content() as $i => $p) {
                $p = $this->autoFormat($p);
                $lines = $this->ansiWordwrap($p, $this->minWidth - 2);
                $partLines = [];

                if ($part->spaced && $i !== 0) {
                    $partLines[] = '';
                }

                foreach ($lines as $index => $line) {
                    if ($index === 0) {
                        $partLines[] = $this->dim('·').' '.$line;
                    } else {
                        $partLines[] = '  '.$line;
                    }
                }

                $finalLines[] = implode(PHP_EOL, $partLines);
            }

            return $finalLines;
        }

        if ($part instanceof NumberedList) {
            $finalLines = [];

            foreach ($part->content() as $i => $p) {
                // +1 for "."
                $widestNumber = mb_strwidth((string) count($part->content())) + 1;
                $partLines = [];
                // -1 for ' ' after number
                $p = $this->autoFormat($p);
                $lines = $this->ansiWordwrap($p, $this->minWidth - $widestNumber - 1);

                if ($part->spaced && $i !== 0) {
                    $partLines[] = '';
                }

                foreach ($lines as $index => $line) {
                    if ($index === 0) {
                        $partLines[] = $this->dim(mb_str_pad(($i + 1).'.', $widestNumber, pad_type: STR_PAD_LEFT)).' '.$line;
                    } else {
                        // +1 for ' ' after number
                        $partLines[] = str_repeat(' ', $widestNumber + 1).$line;
                    }
                }

                $finalLines[] = implode(PHP_EOL, $partLines);
            }

            return $finalLines;
        }

        if ($part instanceof KeyValueList) {
            $items = $part->content();
            $keys = array_keys($items);
            $widestKey = max(array_map(fn ($key) => mb_strwidth($key), $keys));

            $finalLines = [];

            foreach ($items as $key => $value) {
                $paddedKey = mb_str_pad($key, $widestKey);
                $value = $this->autoFormat($value);
                $lines = $this->ansiWordwrap($value, $this->minWidth - $widestKey - 2);

                foreach ($lines as $index => $line) {
                    if ($index === 0) {
                        $finalLines[] = $this->dim($paddedKey).'  '.$line;
                    } else {
                        $finalLines[] = str_repeat(' ', $widestKey + 2).$line;
                    }
                }
            }

            return $finalLines;
        }

        if ($part instanceof Link) {
            return (string) $part;
        }

        return $this->autoFormat(implode(PHP_EOL, $part->content()));
    }

    /**
     * Auto-format the text by applying styles to specific patterns, such as inline code blocks.
     */
    protected function autoFormat(string $text): string
    {
        // Format inline code blocks wrapped in backticks with cyan color.
        return preg_replace('/`([^`]+)`/', $this->cyan('`$1`'), $text);
    }
}
