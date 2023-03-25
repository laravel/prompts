<?php

namespace Laravel\Prompts;

use RuntimeException;

class Spinner extends Prompt
{
    /**
     * How long to wait between rendering each frame.
     *
     * @var int
     */
    public $interval = 100;

    /**
     * The number of times the spinner has been rendered.
     *
     * @var int
     */
    public $count = 0;

    /**
     * Whether the spinner can only be rendered once.
     *
     * @var bool
     */
    public $static = false;

    /**
     * Create a new Spinner instance.
     *
     * @param  string  $message
     */
    public function __construct(public $message = '') {
        //
    }

    /**
     * Render the spinner and execute the callback.
     *
     * @param  \Closure  $callback
     * @return mixed
     */
    public function spin($callback)
    {
        if (! function_exists('pcntl_fork')) {
            $this->renderStatically($callback);
        }

        $this->hideCursor();

        $originalAsync = pcntl_async_signals(true);

        pcntl_signal(SIGINT, function () {
            $this->showCursor();
            exit();
        });

        try {
            $this->render();

            $pid = pcntl_fork();

            if ($pid === 0) {
                while (true) {
                    $this->render();

                    $this->count++;

                    usleep($this->interval * 1000);
                }
            } else {
                $result = $callback();
                posix_kill($pid, SIGHUP);
                $lines = explode(PHP_EOL, $this->prevFrame);
                $this->moveCursor(-999, -count($lines) + 1);
                $this->eraseDown();
                $this->showCursor();
                pcntl_async_signals($originalAsync);
                pcntl_signal(SIGINT, SIG_DFL);

                return $result;
            }
        } catch (\Throwable $e) {
            $this->showCursor();
            pcntl_async_signals($originalAsync);
            pcntl_signal(SIGINT, SIG_DFL);

            throw $e;
        }
    }

    /**
     * Render a static version of the spinner.
     *
     * @param  \Closure  $callback
     * @return mixed
     */
    protected function renderStatically($callback)
    {
        $this->static = true;

        $this->render();

        return $callback();
    }

    /**
     * Disable prompting for input.
     *
     * @return void
     */
    public function prompt()
    {
        throw new RuntimeException('Spinner cannot be prompted.');
    }

    /**
     * Get the current value of the prompt.
     *
     * @return mixed
     */
    public function value()
    {
        return null;
    }
}
