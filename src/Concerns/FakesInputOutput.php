<?php

namespace Laravel\Prompts\Concerns;

use Laravel\Prompts\Output\ConsoleOutput;
use Laravel\Prompts\Terminal;
use Mockery\MockInterface;
use PHPUnit\Framework\Assert;
use RuntimeException;
use Tests\PromptFake;

trait FakesInputOutput
{
    /**
     * Fake the terminal and queue key presses to be simulated.
     *
     * @internal
     *
     * @param  array<string>  $keys
     */
    public static function fake(array $keys = []): PromptFake
    {
        // Force interactive mode when testing because we will be mocking the terminal.
        static::interactive();

        $terminal = \Mockery::mock(Terminal::class);
        $output = \Mockery::mock(ConsoleOutput::class);

        static::$terminal = $terminal;
        static::$renderCompleteFrames = true;

        self::setOutput($output);

        return new PromptFake($terminal, $output, $keys);
    }

    /**
     * Assert that the output contains the given string.
     */
    public static function assertOutputContains(string $string): void
    {
        Assert::assertStringContainsString($string, implode('', static::buffer()));
    }

    /**
     * Assert that the output doesn't contain the given string.
     */
    public static function assertOutputDoesntContain(string $string): void
    {
        Assert::assertStringNotContainsString($string, implode('', static::buffer()));
    }

    /**
     * Assert that the stripped output contains the given string.
     */
    public static function assertStrippedOutputContains(string $string): void
    {
        Assert::assertStringContainsString($string, implode('', static::strippedBuffer()));
    }

    /**
     * Assert that the stripped output doesn't contain the given string.
     */
    public static function assertStrippedOutputDoesntContain(string $string): void
    {
        Assert::assertStringNotContainsString($string, implode('', static::strippedBuffer()));
    }

    /**
     * Get the buffered console output.
     *
     * @return list<string>
     */
    public static function buffer(): array
    {
        if (! static::output() instanceof MockInterface) {
            throw new RuntimeException('Prompt must be faked before accessing the buffer.');
        }

        // @phpstan-ignore-next-line method.notFound
        return static::output()->buffer();
    }

    /**
     * Get the buffered console output, stripped of escape sequences.
     *
     * @return list<string>
     */
    public static function strippedBuffer(): array
    {
        return array_map(
            fn ($line) => preg_replace("/\e\[[0-9;?]*[A-Za-z]/", '', $line),
            static::buffer()
        );
    }
}
