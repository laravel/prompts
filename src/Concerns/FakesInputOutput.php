<?php

namespace Laravel\Prompts\Concerns;

use Laravel\Prompts\Output\BufferedConsoleOutput;
use Laravel\Prompts\Terminal;
use PHPUnit\Framework\Assert;
use RuntimeException;

trait FakesInputOutput
{
    /**
     * Fake the terminal and queue key presses to be simulated.
     *
     * @param  array<string>  $keys
     */
    public static function fake(array $keys = []): void
    {
        $mock = \Mockery::mock(Terminal::class);

        $mock->shouldReceive('write')->byDefault();
        $mock->shouldReceive('exit')->byDefault();
        $mock->shouldReceive('setTty')->byDefault();
        $mock->shouldReceive('restoreTty')->byDefault();
        $mock->shouldReceive('cols')->byDefault()->andReturn(80);
        $mock->shouldReceive('lines')->byDefault()->andReturn(24);

        foreach ($keys as $key) {
            $mock->shouldReceive('read')->once()->andReturn($key);
        }

        static::$terminal = $mock;

        self::setOutput(new BufferedConsoleOutput());
    }

    /**
     * Assert that the output contains the given string.
     */
    public static function assertOutputContains(string $string): void
    {
        if (! static::output() instanceof BufferedConsoleOutput) {
            throw new RuntimeException('Prompt must be faked before asserting output.');
        }

        Assert::assertStringContainsString($string, static::output()->content());
    }
}
