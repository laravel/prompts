<?php

namespace Laravel\Prompts;

use RuntimeException;

class Spinner extends Prompt
{
    /**
     * The current frame of the spinner.
     *
     * @var string
     */
    public $frame;

    /**
     * Create a new Spinner instance.
     *
     * @param  string  $message
     * @param  array<int, string>  $frames
     * @param  int  $interval
     */
    public function __construct(
        public $message = '',
        protected $frames = ['◒', '◐', '◓', '◑'],
        protected $interval = 100,
    ) {
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
            $this->renderStatic();

            return $callback();
        }

        $this->hideCursor();

        $originalAsync = pcntl_async_signals(true);
        pcntl_signal(SIGINT, function () {
            $this->showCursor();
            exit();
        });

        try {
            $this->frame = $this->frames[0];
            $this->render();

            $pid = pcntl_fork();

            if ($pid === 0) {
                $i = 0;
                while (true) {
                    $this->frame = $this->frames[$i];
                    $this->render();
                    $i = ($i + 1) % count($this->frames);
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
     * @return void
     */
    protected function renderStatic()
    {
        $this->frame = '○';

        $this->render();
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
