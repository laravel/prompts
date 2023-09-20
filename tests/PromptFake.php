<?php

namespace Tests;

use Mockery\MockInterface;
use PHPUnit\Framework\Assert;

/**
 * @internal
 */
class PromptFake
{
    /**
     * The queue of keys and output to be mocked and asserted against.
     *
     * @var list<array{type: string, content: string, raw?: bool}>
     */
    protected array $queue = [];

    /**
     * The console output buffer.
     *
     * @var list<string>
     */
    protected array $buffer = [];

    /**
     * Create a new PromptFake instance.
     *
     * @param  list<string>  $keys
     */
    public function __construct(protected MockInterface $terminal, protected MockInterface $output, array $keys = [])
    {
        $terminal->shouldIgnoreMissing();
        $terminal->shouldReceive('cols')->byDefault()->andReturn(80);
        $terminal->shouldReceive('lines')->byDefault()->andReturn(24);
        $terminal->shouldReceive('read')->andReturnUsing($this->readKey(...));

        $output->shouldReceive('newLinesWritten')->andReturn(1);
        $output->shouldReceive('write')->andReturnUsing($this->writeOutput(...));
        $output->shouldReceive('writeDirectly')->andReturnUsing($this->writeOutput(...));
        $output->shouldReceive('buffer')->andReturnUsing(fn () => $this->buffer);

        if (count($keys) > 0) {
            $this->receives($keys);
        }
    }

    /**
     * Queue mock key presses.
     *
     * @param  list<string>|string  $keys
     */
    public function receives(array|string $keys): static
    {
        if (! is_array($keys)) {
            $keys = [$keys];
        }

        foreach ($keys as $key) {
            $this->queue[] = [
                'type' => 'key',
                'content' => $key,
            ];
        }

        return $this;
    }

    /**
     * Queue output expectations.
     *
     * @param  list<string>  $keys
     */
    public function outputs(string $output, bool $raw = false): static
    {
        $this->queue[] = [
            'type' => 'output',
            'content' => $output,
            'raw' => $raw,
        ];

        return $this;
    }

    /**
     * Queue an expectation that the cursor will be hidden.
     */
    public function hidesCursor(): static
    {
        return $this->outputs("\e[?25l", raw: true);
    }

    /**
     * Queue an expectation that the cursor will be shown.
     */
    public function showsCursor(): static
    {
        return $this->outputs("\e[?25h", raw: true);
    }

    /**
     * Handle reading a key.
     */
    protected function readKey(): string
    {
        $next = array_shift($this->queue);

        if ($next === null) {
            Assert::fail('Expected more keys. Did you forget to submit the prompt with `Key::ENTER`?');
        }

        Assert::assertSame('key', $next['type'], "Expected a key, got {$next['type']}: {$next['content']}");

        return $next['content'];
    }

    /**
     * Handle output written.
     */
    protected function writeOutput(string $output): void
    {
        $this->buffer[] = $output;

        if (($this->queue[0]['type'] ?? null) !== 'output') {
            return;
        }

        $next = array_shift($this->queue);

        if ($this->strip($output) === '' && empty($next['raw'])) {
            // Compare escaped versions to make them visible in the assertion error.
            Assert::assertSame(
                $this->escape($output),
                $this->escape($next['content']),
                $output === "\e[?25l" ? 'Do you need a `hidesCursor` expectation?' : null,
            );
        } else {
            Assert::assertSame(
                empty($next['raw']) ? $this->strip($output) : $output,
                $next['content'],
            );
        }
    }

    /**
     * Strip escape sequences from the given string.
     */
    private function strip(string $string): string
    {
        return preg_replace("/\e\[[0-9;?]*[A-Za-z]/", '', $string);
    }

    /**
     * Escape escape sequences from the given string.
     */
    private function escape(string $string): string
    {
        return preg_replace("/\e/", '\e', $string);
    }
}
