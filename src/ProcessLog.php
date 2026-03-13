<?php

namespace Laravel\Prompts;

use Closure;
use Laravel\Prompts\Support\Logger;
use RuntimeException;

class ProcessLog extends Prompt
{
    use Concerns\Truncation;

    /**
     * How long to wait between rendering each frame.
     */
    public int $interval = 100;

    /**
     * The number of times the spinner has been rendered.
     */
    public int $count = 0;

    /**
     * Whether the spinner can only be rendered once.
     */
    public bool $static = false;

    /**
     * The process ID after forking.
     */
    protected int $pid;

    /**
     * The socket for IPC communication.
     *
     * @var resource|null
     */
    protected $socket;

    /**
     * Messages received from the parent process.
     *
     * @var array<int, string>
     */
    public array $logs = [];

    protected string $fullMessage = '';

    /**
     * Messages received from the parent process.
     *
     * @var list<array{type: string, message: string}>
     */
    public array $stableMessages = [];

    /**
     * The maximum number of stable messages to display.
     */
    public int $maxStableMessages = 10;

    /**
     * The identifier for the task log.
     */
    public string $identifier = '';

    /**
     * Whether the process log has finished.
     */
    public bool $finished = false;

    /**
     * The partial line.
     */
    public string $partialLine = '';

    /**
     * The partial line timeout.
     */
    public int $partialLineLogsCount = 0;

    /**
     * Create a new ProcessLog instance.
     */
    public function __construct(
        public string $message = '',
        public int $limit = 10,
    ) {
        $this->identifier = uniqid();
    }

    /**
     * Render the spinner and execute the callback.
     *
     * @template TReturn of mixed
     *
     * @param  \Closure(Logger): TReturn  $callback
     * @return TReturn
     */
    public function run(Closure $callback): mixed
    {
        $maxHeight = $this->terminal()->lines() - 10;

        $this->limit = min($this->limit, $maxHeight);
        // Max height - limit - divider - spinner message
        $this->maxStableMessages = max(0, $maxHeight - $this->limit - 2);

        $this->capturePreviousNewLines();

        if (! function_exists('pcntl_fork')) {
            return $this->renderStatically($callback);
        }

        $originalAsync = pcntl_async_signals(true);

        pcntl_signal(SIGINT, fn() => exit());

        try {
            $this->hideCursor();
            $this->render();

            $sockets = stream_socket_pair(STREAM_PF_UNIX, STREAM_SOCK_STREAM, STREAM_IPPROTO_IP);

            if ($sockets === false) {
                return $this->renderStatically($callback);
            }

            $this->pid = pcntl_fork();

            if ($this->pid === 0) {
                fclose($sockets[1]);
                $childSocket = $sockets[0];
                stream_set_blocking($childSocket, false);

                while (true) { // @phpstan-ignore-line
                    $this->receiveMessages($childSocket);

                    if (! $this->finished) {
                        $this->render();
                        $this->count++;
                    }

                    usleep($this->interval * 1000);
                }
            } else {
                fclose($sockets[0]);
                $this->socket = $sockets[1];

                $logger = new Logger($this->identifier, $this->socket);
                $result = $callback($logger);

                if ($this->socket !== null) {
                    // Send a reset message to the parent process to reset the terminal.
                    fwrite($this->socket, $this->identifier . '_' . 'reset:' . ($originalAsync ? 1 : 0) . PHP_EOL);
                    usleep($this->interval * 2000);
                }

                return $result;
            }
        } catch (\Throwable $e) {
            $this->resetTerminal($originalAsync);

            throw $e;
        }
    }

    /**
     * Receive and process messages from the parent process.
     *
     * @param  resource  $socket
     */
    protected function receiveMessages($socket): void
    {
        while (($line = fgets($socket)) !== false) {
            $lines = $this->processLine($line);

            foreach ($lines as $line) {
                preg_match('/^' . $this->identifier . '_(success|warning|error|label|reset|partial|commit):/', $line, $matches);

                if (count($matches) > 0 && ! in_array($matches[1], ['partial', 'commit'])) {
                    $this->partialLine = '';

                    $stableLine = str_replace($this->identifier . '_' . $matches[1] . ':', '', $line);

                    if ($matches[1] === 'reset') {
                        $this->resetTerminal((bool) $stableLine);

                        continue;
                    }

                    if ($matches[1] === 'label') {
                        $this->message = $stableLine;
                    } else {
                        $this->stableMessages[] = ['type' => $matches[1], 'message' => $stableLine];
                        $this->logs = [];
                    }
                } elseif ($matches[1] === 'commit') {
                    $this->fullMessage .= PHP_EOL;
                    $this->logs = $this->wordwrapWithAnsi($this->fullMessage, $this->terminal()->cols() - 10);
                } elseif ($matches[1] === 'partial') {
                    $partialLine = str_replace($this->identifier . '_' . $matches[1] . ':', '', $line);

                    $this->fullMessage .= $partialLine;
                    $this->logs = $this->wordwrapWithAnsi($this->fullMessage, $this->terminal()->cols() - 10);

                    // if (strlen($this->partialLine) === 0) {
                    //     // We're starting a new partial line, so we need to capture the current number of logs.
                    //     $this->partialLineLogsCount = count($this->logs);
                    // }

                    // $this->partialLine .= $partialLine;
                    // $this->logs = array_slice($this->logs, 0, $this->partialLineLogsCount);

                    // $lines = $this->processLine($this->partialLine);

                    // foreach ($lines as $line) {
                    //     // Ensure a leading space after every "move to start + erase line"
                    //     if (preg_match('/\e\[(?:1)?G\e\[2K(?! )/', $line)) {
                    //         $line = preg_replace(
                    //             '/\e\[(?:1)?G\e\[2K(?! )/',
                    //             "\e[1G\e[2K ",
                    //             $line
                    //         );
                    //     } else {
                    //         $line = ' ' . $line;
                    //     }

                    //     $this->logs[] = $line;
                    // }
                } else {
                    $this->partialLine = '';
                    // Ensure a leading space after every "move to start + erase line"
                    if (preg_match('/\e\[(?:1)?G\e\[2K(?! )/', $line)) {
                        $line = preg_replace(
                            '/\e\[(?:1)?G\e\[2K(?! )/',
                            "\e[1G\e[2K ",
                            $line
                        );
                    } else {
                        $line = ' ' . $line;
                    }

                    // $this->logs[] = $line;
                    $this->fullMessage .= $line;
                    $this->logs = $this->wordwrapWithAnsi($this->fullMessage, $this->terminal()->cols() - 10);
                }
            }

            // while (count($this->stableMessages) > $this->maxStableMessages) {
            //     array_shift($this->stableMessages);
            // }

            // while (count($this->logs) > $this->limit) {
            //     array_shift($this->logs);
            // }
        }
    }

    /**
     * Process a line by stripping ANSI codes, word wrapping, and re-applying styles.
     *
     * @return list<string>
     */
    protected function processLine(string $line): array
    {
        $line = rtrim($line, PHP_EOL);

        if (empty($line)) {
            return [];
        }

        $plainText = $this->stripEscapeSequences($line);

        if (mb_strwidth($plainText) <= $this->terminal()->cols() - 10) {
            return [$line];
        }

        return $this->wordwrapWithAnsi($line, 60);
    }

    /**
     * Reset the terminal.
     */
    protected function resetTerminal(bool $originalAsync): void
    {
        $this->finished = true;

        pcntl_async_signals($originalAsync);
        pcntl_signal(SIGINT, SIG_DFL);

        if ($this->socket !== null) {
            fclose($this->socket);
            $this->socket = null;
        }

        $this->eraseRenderedLines();
    }

    /**
     * Render a static version of the spinner.
     *
     * @template TReturn of mixed
     *
     * @param  \Closure(Logger): TReturn  $callback
     * @return TReturn
     */
    protected function renderStatically(Closure $callback): mixed
    {
        $this->static = true;

        try {
            $this->hideCursor();
            $this->render();

            $logger = new Logger($this->identifier);
            $result = $callback($logger);
        } finally {
            $this->eraseRenderedLines();
        }

        return $result;
    }

    /**
     * Disable prompting for input.
     *
     * @throws \RuntimeException
     */
    public function prompt(): never
    {
        throw new RuntimeException('Spinner cannot be prompted.');
    }

    /**
     * Get the current value of the prompt.
     */
    public function value(): bool
    {
        return true;
    }

    /**
     * Clear the lines rendered by the spinner.
     */
    protected function eraseRenderedLines(): void
    {
        $lines = explode(PHP_EOL, $this->prevFrame);
        $this->moveCursor(-999, -count($lines) + 1);
        $this->eraseDown();
    }

    /**
     * Clean up after the spinner.
     */
    public function __destruct()
    {
        if (! empty($this->pid)) {
            posix_kill($this->pid, SIGHUP);
        }

        parent::__destruct();
    }
}
