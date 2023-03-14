<?php

namespace Laravel\Prompts\Concerns;

use Closure;

trait Events
{
    /**
     * The registered event listeners.
     *
     * @var array<string, array<int, Closure>>
     */
    protected $listeners = [];

    /**
     * Register an event listener.
     *
     * @param  string  $event
     * @param  \Closure  $callback
     * @return void
     */
    public function on($event, $callback)
    {
        $this->listeners[$event][] = $callback;
    }

    /**
     * Emit an event.
     *
     * @param  string  $event
     * @param  mixed  ...$data
     * @return void
     */
    public function emit($event, ...$data) {
        foreach ($this->listeners[$event] ?? [] as $listener) {
            $listener(...$data);
        }
    }
}
