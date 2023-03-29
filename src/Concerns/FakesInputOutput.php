<?php

namespace Laravel\Prompts\Concerns;

use Laravel\Prompts\Terminal;
use Mockery\MockInterface;

trait FakesInputOutput
{
    /**
     * Fake the terminal and queue key presses to be simulated.
     *
     * @param  array<string>  $keys
     */
    public static function fake(array $keys): MockInterface
    {
        $mock = \Mockery::mock(Terminal::class);

        $mock->shouldReceive('write')->byDefault();
        $mock->shouldReceive('exit')->byDefault();
        $mock->expects('setTty')->byDefault();
        $mock->expects('restoreTty')->byDefault();

        foreach ($keys as $key) {
            $mock->shouldReceive('read')->once()->andReturn($key);
        }

        static::terminal($mock);

        return $mock;
    }
}
