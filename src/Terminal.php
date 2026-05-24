<?php

namespace Laravel\Prompts;

use Closure;
use ReflectionClass;
use RuntimeException;
use Symfony\Component\Console\Terminal as SymfonyTerminal;

class Terminal
{
    /**
     * The initial TTY mode.
     */
    protected ?string $initialTtyMode;

    /**
     * The initial native terminal mode.
     */
    protected ?string $initialNativeTtyMode;

    /**
     * Whether the terminal supports true color.
     */
    protected static ?bool $trueColorSupport = null;

    /**
     * The terminal's foreground color as an RGB array.
     *
     * @var array{int, int, int}|null
     */
    protected static ?array $foregroundColor = null;

    /**
     * The terminal's background color as an RGB array.
     *
     * @var array{int, int, int}|null
     */
    protected static ?array $backgroundColor = null;

    /**
     * The Symfony Terminal instance.
     */
    protected SymfonyTerminal $terminal;

    /**
     * Create a new Terminal instance.
     */
    public function __construct()
    {
        $this->terminal = new SymfonyTerminal;
    }

    /**
     * Read a line from the terminal.
     */
    public function read(): string
    {
        if ($this->hasNativeTerminal()) {
            $key = $this->readNativeKey();

            return is_string($key) ? $this->normalizeNativeKey($key) : '';
        }

        $input = fread(STDIN, 1024);

        return $input !== false ? $input : '';
    }

    /**
     * Set the TTY mode.
     */
    public function setTty(string $mode): void
    {
        if ($this->hasNativeTerminal()) {
            if (! isset($this->initialNativeTtyMode)) {
                $nativeMode = $this->enableNativeRawMode();

                if (! is_string($nativeMode)) {
                    throw new RuntimeException('Failed to enable terminal raw mode.');
                }

                $this->initialNativeTtyMode = $nativeMode;
            }

            return;
        }

        $this->initialTtyMode ??= $this->exec('stty -g');

        $this->exec("stty $mode");
    }

    /**
     * Restore the initial TTY mode.
     */
    public function restoreTty(): void
    {
        if (isset($this->initialNativeTtyMode)) {
            $this->restoreNativeMode($this->initialNativeTtyMode);

            $this->initialNativeTtyMode = null;

            return;
        }

        if (isset($this->initialTtyMode)) {
            $this->exec("stty {$this->initialTtyMode}");

            $this->initialTtyMode = null;
        }
    }

    /**
     * Get the number of columns in the terminal.
     */
    public function cols(): int
    {
        if ($this->hasNativeTerminal() && ($size = $this->nativeSize()) !== false) {
            return $size['columns'];
        }

        return $this->terminal->getWidth();
    }

    /**
     * Get the number of lines in the terminal.
     */
    public function lines(): int
    {
        if ($this->hasNativeTerminal() && ($size = $this->nativeSize()) !== false) {
            return $size['rows'];
        }

        return $this->terminal->getHeight();
    }

    /**
     * (Re)initialize the terminal dimensions.
     */
    public function initDimensions(): void
    {
        if ($this->hasNativeTerminal()) {
            return;
        }

        (new ReflectionClass($this->terminal))
            ->getMethod('initDimensions')
            ->invoke($this->terminal);
    }

    /**
     * Determine if the terminal is interactive.
     */
    public function interactive(): bool
    {
        if ($this->hasNativeTerminal()) {
            return $this->nativeStdinIsTty();
        }

        return stream_isatty(STDIN);
    }

    /**
     * Determine if the native terminal extension is available.
     */
    public function hasNativeTerminal(): bool
    {
        return extension_loaded('terminal')
            && function_exists('terminal_read_key')
            && function_exists('terminal_enable_raw_mode')
            && function_exists('terminal_restore_mode')
            && function_exists('terminal_is_tty')
            && function_exists('terminal_get_size')
            && defined('TERMINAL_STDIN');
    }

    /**
     * Exit the interactive session.
     */
    public function exit(): void
    {
        exit(1);
    }

    /**
     * Execute the given command and return the output.
     */
    protected function exec(string $command): string
    {
        $process = proc_open($command, [
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ], $pipes);

        if (! $process) {
            throw new RuntimeException('Failed to create process.');
        }

        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        $code = proc_close($process);

        if ($code !== 0 || $stdout === false) {
            throw new RuntimeException(trim($stderr ?: "Unknown error (code: $code)"), $code);
        }

        return $stdout;
    }

    /**
     * Read a key from the native terminal extension.
     */
    protected function readNativeKey(): string|false
    {
        $readKey = $this->nativeFunction('terminal_read_key');

        if ($readKey === null) {
            return false;
        }

        $key = $readKey();

        return is_string($key) ? $key : false;
    }

    /**
     * Enable raw mode through the native terminal extension.
     */
    protected function enableNativeRawMode(): string|false
    {
        $enableRawMode = $this->nativeFunction('terminal_enable_raw_mode');

        if ($enableRawMode === null) {
            return false;
        }

        $mode = $enableRawMode();

        return is_string($mode) ? $mode : false;
    }

    /**
     * Restore the native terminal mode.
     */
    protected function restoreNativeMode(string $mode): bool
    {
        $restoreMode = $this->nativeFunction('terminal_restore_mode');

        return $restoreMode !== null && $restoreMode($mode) === true;
    }

    /**
     * Determine if native standard input is a TTY.
     */
    protected function nativeStdinIsTty(): bool
    {
        $isTty = $this->nativeFunction('terminal_is_tty');

        return $isTty !== null && $isTty((int) constant('TERMINAL_STDIN')) === true;
    }

    /**
     * Get the native terminal size.
     *
     * @return array{columns:int, rows:int}|false
     */
    protected function nativeSize(): array|false
    {
        $getSize = $this->nativeFunction('terminal_get_size');

        if ($getSize === null) {
            return false;
        }

        $size = $getSize();

        if (! is_array($size) || ! isset($size['columns'], $size['rows'])) {
            return false;
        }

        return ['columns' => (int) $size['columns'], 'rows' => (int) $size['rows']];
    }

    /**
     * Resolve a native terminal function.
     */
    protected function nativeFunction(string $function): ?Closure
    {
        return function_exists($function) ? Closure::fromCallable($function) : null;
    }

    /**
     * Normalize native key names to the sequences Prompts already understands.
     */
    protected function normalizeNativeKey(string $key): string
    {
        return match ($key) {
            'up' => Key::UP,
            'down' => Key::DOWN,
            'right' => Key::RIGHT,
            'left' => Key::LEFT,
            'enter' => Key::ENTER,
            'backspace' => Key::BACKSPACE,
            'escape' => Key::ESCAPE,
            'delete' => Key::DELETE,
            'tab' => Key::TAB,
            'home' => Key::HOME[0],
            'end' => Key::END[0],
            'pageup' => Key::PAGE_UP,
            'pagedown' => Key::PAGE_DOWN,
            default => $key,
        };
    }

    /**
     * Determine if the terminal supports true color (24-bit).
     */
    public function supportsTrueColor(): bool
    {
        return static::$trueColorSupport ??= in_array(getenv('COLORTERM'), ['truecolor', '24bit']);
    }

    /**
     * Get the terminal's foreground color as an RGB array.
     *
     * @return array{int, int, int}
     */
    public function foregroundColor(): array
    {
        if (static::$foregroundColor === null) {
            $this->queryColors();
        }

        return static::$foregroundColor;
    }

    /**
     * Get the terminal's background color as an RGB array.
     *
     * @return array{int, int, int}
     */
    public function backgroundColor(): array
    {
        if (static::$backgroundColor === null) {
            $this->queryColors();
        }

        return static::$backgroundColor;
    }

    /**
     * Query the terminal for foreground and background colors in a single shot.
     */
    protected function queryColors(): void
    {
        if (PHP_OS_FAMILY === 'Windows') {
            static::$foregroundColor = [204, 204, 204];
            static::$backgroundColor = [0, 0, 0];

            return;
        }

        $savedStty = trim((string) shell_exec('stty -g < /dev/tty'));

        shell_exec('stty raw -echo min 0 time 1 < /dev/tty');

        fwrite(STDOUT, "\e]10;?\e\\\e]11;?\e\\");
        fflush(STDOUT);

        $ttyIn = fopen('/dev/tty', 'r');

        if ($ttyIn === false) {
            static::$foregroundColor = [204, 204, 204];
            static::$backgroundColor = [0, 0, 0];

            return;
        }

        $response = fread($ttyIn, 200);
        fclose($ttyIn);

        shell_exec("stty {$savedStty} < /dev/tty");

        preg_match_all('/rgb:([0-9a-f]+)\/([0-9a-f]+)\/([0-9a-f]+)/i', $response ?: '', $matches, PREG_SET_ORDER);

        $parse = fn (array $m) => [
            (int) (hexdec($m[1]) / (strlen($m[1]) === 4 ? 257 : 1)),
            (int) (hexdec($m[2]) / (strlen($m[2]) === 4 ? 257 : 1)),
            (int) (hexdec($m[3]) / (strlen($m[3]) === 4 ? 257 : 1)),
        ];

        static::$foregroundColor = isset($matches[0]) ? $parse($matches[0]) : [204, 204, 204];
        static::$backgroundColor = isset($matches[1]) ? $parse($matches[1]) : [0, 0, 0];
    }
}
