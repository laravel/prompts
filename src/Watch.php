<?php

namespace Laravel\Prompts;

use Closure;
use Mockery\MockInterface;
use PHPUnit\Framework\Assert;
use RuntimeException;
use ValueError;

class Watch extends Prompt
{
    /**
     * How many times to fake an iteration.
     */
    protected static int $fakeTimes = 1;

    /**
     * count of faked iterations.
     */
    protected int $fakedTimes = 0;

    /**
     * Faking sleep or not.
     */
    protected static bool $fakeSleep = true;

    /**
     * The amount of seconds slept during intervals in total.
     */
    protected static int $sleptSeconds = 0;

    /**
     * The closure to execute on interval.
     */
    protected Closure $watch;

    /**
     * The interval between updates.
     */
    protected int $interval;

    /**
     * Create a new Watch instance.
     */
    public function __construct(callable $watch, ?int $interval = 2)
    {
        static::$sleptSeconds = 0;
        $this->watch = $watch(...);
        $this->interval = $interval ?? 2;

        if ($this->interval < 0) {
            throw new ValueError('watch interval must be greater than or equal to 0');
        }
    }
    /**
     * displays the watched output and updates after the specified interval.
     */
    public function render(): void
    {
        $faked = static::isFaked();

        static::interactive(false);

        while (!$faked || $this->fakedTimes < static::$fakeTimes) {

            parent::render();

            if ($faked) {
                $this->fakedTimes++;

                if ($this->fakedTimes >= static::$fakeTimes) {

                    if (static::$terminal instanceof MockInterface) {
                        $this->state = 'submit';
                        static::$terminal->expects('read')->zeroOrMoreTimes()->andReturn(false);
                    }
                    static::$fakeSleep = true;
                    break;
                }

                if (static::$fakeSleep) {
                    static::$sleptSeconds += $this->interval;
                    continue;
                }
            }

            sleep($this->interval);
        }
    }

    /**
     * Render the prompt using the active theme.
     * Overrides default behaviour to pass along the current output.
     */
    protected function renderTheme(): string
    {
        $renderer = static::getRenderer();

        return $renderer($this->watch, static::output());
    }

    /**
     * Get the value of the prompt.
     */
    public function value(): bool
    {
        return true;
    }

    /**
     * Tell Prompt how many iterations to fake.
     */
    public static function fakeTimes(int $times): void
    {
        if (!static::isFaked()) {
            throw new RuntimeException('Prompt must be faked before faking iterations.');
        }

        static::$fakeTimes = $times;
    }

    /**
     * Asserts the amount of seconds slept during intervals in total.
     */
    public static function assertSecondsSleptBetweenIntervals(int $seconds): void
    {
        if (!static::isFaked()) {
            throw new RuntimeException('Prompt must be faked before asserting.');
        }

        Assert::assertEquals($seconds, static::$sleptSeconds);
    }

    /**
     * By default, when Prompt is faked, the intervals are faked.
     * Use this to actually sleep between updates.
     */
    public static function shouldNotFakeIntervalSleep(): void
    {
        if (!self::isFaked()) {
            throw new RuntimeException('Not faking sleep makes no sense when not faking Prompt.');
        }

        static::$fakeSleep = false;
    }
}
