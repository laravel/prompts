<?php

namespace Laravel\Prompts;

use FFI;
use Throwable;

/**
 * Native Windows console driver.
 *
 * Windows consoles cannot drive Prompts the way Unix terminals do: there
 * is no stty, and fread(STDIN) on a raw-mode Windows console returns an
 * empty string immediately instead of blocking. This driver reaches the
 * operating system directly through FFI instead:
 *
 * - kernel32 GetConsoleMode/SetConsoleMode switch stdout into virtual
 *   terminal processing (so the ANSI sequences Prompts emits are
 *   interpreted) and strip processed/line/echo input from stdin (so
 *   keystrokes arrive one at a time, without echo or line buffering).
 * - msvcrt _getwch performs the blocking single-keystroke read; it is the
 *   only reliable blocking reader on Windows without implementing a full
 *   INPUT_RECORD driver. Each keystroke is translated into the same
 *   sequence a Unix terminal would send, so nothing downstream changes.
 *
 * The original console modes are captured by enable() and reapplied by
 * restore(), which is also registered as a shutdown function so an exit()
 * cannot leak raw mode into the user's shell.
 *
 * Bindings are built lazily on first use, and every FFI interaction is
 * guarded, so environments without the FFI extension never fatal: enable()
 * simply returns false and callers fall back. The same applies to pipes
 * and pseudo terminals such as mintty, where Win32 console APIs do not
 * apply at all (GetConsoleMode fails there by design).
 */
class WindowsConsole
{
    /**
     * The std handle identifiers accepted by GetStdHandle.
     */
    private const STD_INPUT_HANDLE = -10;

    private const STD_OUTPUT_HANDLE = -11;

    /**
     * Input mode flags stripped to obtain unbuffered, unechoed keystrokes.
     */
    private const ENABLE_PROCESSED_INPUT = 0x0001;

    private const ENABLE_LINE_INPUT = 0x0002;

    private const ENABLE_ECHO_INPUT = 0x0004;

    /**
     * Output mode flag requesting interpretation of ANSI escape sequences.
     */
    private const ENABLE_VIRTUAL_TERMINAL_PROCESSING = 0x0004;

    private const KERNEL32_DECLARATIONS = <<<'C'
        void* GetStdHandle(int handle);
        int GetConsoleMode(void* h, unsigned int* mode);
        int SetConsoleMode(void* h, unsigned int mode);
        C;

    private const MSVCRT_DECLARATIONS = 'int _getwch(void);';

    /**
     * The kernel32 bindings, built on first use.
     */
    protected ?FFI $kernel32 = null;

    /**
     * The msvcrt bindings, built on first use.
     */
    protected ?FFI $msvcrt = null;

    /**
     * The console input mode captured before raw mode was enabled.
     */
    protected ?int $originalInputMode = null;

    /**
     * The console output mode captured before VT processing was enabled.
     */
    protected ?int $originalOutputMode = null;

    /**
     * Whether this driver currently owns the console configuration.
     */
    protected bool $enabled = false;

    /**
     * Configure the console for Prompts: virtual terminal output and raw,
     * silent, unbuffered input.
     *
     * Returns false when the environment cannot support this driver - no
     * FFI extension, a non-CLI SAPI, or streams that are not attached to a
     * real Windows console - signalling callers to fall back. Partial
     * setups are rolled back to the captured modes.
     */
    public function enable(): bool
    {
        if ($this->enabled) {
            return true;
        }

        if (PHP_OS_FAMILY !== 'Windows' || PHP_SAPI !== 'cli' || ! extension_loaded('ffi')) {
            return false;
        }

        $kernel32 = $this->kernel32();

        if ($kernel32 === null) {
            return false;
        }

        try {
            $outputMode = $kernel32->new('unsigned int');
            $inputMode = $kernel32->new('unsigned int');

            // GetConsoleMode failing is the definitive signal that these
            // streams are not attached to a real console: redirected pipes
            // and pseudo terminals such as mintty fail here by design,
            // leaving the caller to fall back.
            if (
                $this->queryMode($kernel32, $this->stdHandle($kernel32, self::STD_OUTPUT_HANDLE), $outputMode) === 0 ||
                $this->queryMode($kernel32, $this->stdHandle($kernel32, self::STD_INPUT_HANDLE), $inputMode) === 0
            ) {
                return false;
            }

            $rawInputMode = $this->modeValue($inputMode) & ~(self::ENABLE_PROCESSED_INPUT | self::ENABLE_LINE_INPUT | self::ENABLE_ECHO_INPUT);

            if ($this->setMode($kernel32, $this->stdHandle($kernel32, self::STD_INPUT_HANDLE), $rawInputMode) === 0) {
                return false;
            }

            if ($this->setMode($kernel32, $this->stdHandle($kernel32, self::STD_OUTPUT_HANDLE), $this->modeValue($outputMode) | self::ENABLE_VIRTUAL_TERMINAL_PROCESSING) === 0) {
                $this->setMode($kernel32, $this->stdHandle($kernel32, self::STD_INPUT_HANDLE), $this->modeValue($inputMode));

                return false;
            }
        } catch (Throwable) {
            return false;
        }

        $this->originalOutputMode = $this->modeValue($outputMode);
        $this->originalInputMode = $this->modeValue($inputMode);
        $this->enabled = true;

        register_shutdown_function(function (): void {
            $this->restore();
        });

        return true;
    }

    /**
     * Reapply the console modes captured by enable().
     *
     * Errors are swallowed deliberately: teardown must never break the
     * application, and a console that disappeared mid-run cannot be fixed
     * from here.
     */
    public function restore(): void
    {
        if (! $this->enabled) {
            return;
        }

        $this->enabled = false;

        $kernel32 = $this->kernel32();

        if ($kernel32 === null) {
            return;
        }

        try {
            $this->setMode($kernel32, $this->stdHandle($kernel32, self::STD_OUTPUT_HANDLE), $this->originalOutputMode ?? 0);
            $this->setMode($kernel32, $this->stdHandle($kernel32, self::STD_INPUT_HANDLE), $this->originalInputMode ?? 0);
        } catch (Throwable) {
            //
        }
    }

    /**
     * Blocking read of a single keystroke, translated into the sequence a
     * Unix terminal would send.
     *
     * fread(STDIN) cannot be used: on a raw-mode Windows console it returns
     * an empty string immediately instead of blocking. _getwch is the only
     * reliable blocking reader without an INPUT_RECORD driver. Navigation
     * keys arrive as prefix-plus-scan-code pairs and unknown pairs are
     * consumed so the next real keystroke is what gets returned. Typed
     * characters arrive as UTF-16 code units and are converted to UTF-8 so
     * non-ASCII input survives.
     */
    public function read(): string
    {
        $msvcrt = $this->msvcrt();

        if ($msvcrt === null) {
            return '';
        }

        for ($attempt = 0; $attempt < 8; $attempt++) {
            try {
                $code = $this->nextCode($msvcrt);
            } catch (Throwable) {
                return '';
            }

            // Arrow, editing and function keys are delivered as a two-read
            // pair: a prefix byte followed by the scan code. Translate the
            // pair into the escape sequence Prompts already understands.
            if ($code === 0x00 || $code === 0xE0) {
                try {
                    $scan = $this->nextCode($msvcrt);
                } catch (Throwable) {
                    return '';
                }

                $sequence = match ($scan) {
                    72 => "\e[A",   // up arrow
                    80 => "\e[B",   // down arrow
                    77 => "\e[C",   // right arrow
                    75 => "\e[D",   // left arrow
                    71 => "\e[H",   // home
                    79 => "\e[F",   // end
                    73 => "\e[5~",  // page up
                    81 => "\e[6~",  // page down
                    83 => "\e[3~",  // delete
                    default => '',
                };

                if ($sequence !== '') {
                    return $sequence;
                }

                continue;
            }

            // Normalize the values Prompts matches by identity, and route
            // wide characters through UTF-16 to UTF-8 conversion.
            return match ($code) {
                13 => "\n",     // enter
                8 => "\177",    // backspace
                3 => "\x03",    // ctrl+c
                27 => "\e",     // escape
                default => $code > 126
                    ? mb_convert_encoding(pack('v', $code), 'UTF-8', 'UTF-16LE')
                    : chr($code),
            };
        }

        return '';
    }

    /**
     * Build the kernel32 bindings on first use.
     */
    protected function kernel32(): ?FFI
    {
        return $this->kernel32 ??= $this->bind('kernel32.dll', self::KERNEL32_DECLARATIONS);
    }

    /**
     * Build the msvcrt bindings on first use.
     */
    protected function msvcrt(): ?FFI
    {
        return $this->msvcrt ??= $this->bind('msvcrt.dll', self::MSVCRT_DECLARATIONS);
    }

    /**
     * Compile FFI declarations against a library, or null when the FFI
     * extension is unavailable or refuses the declarations.
     */
    protected function bind(string $library, string $declarations): ?FFI
    {
        if (! extension_loaded('ffi')) {
            return null;
        }

        try {
            return FFI::cdef($declarations, $library);
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * Fetch one of the process std handles. Returns a null pointer, not
     * null, when the handle does not exist; callers detect that condition
     * through the failing mode calls instead.
     */
    protected function stdHandle(FFI $kernel32, int $handle): mixed
    {
        /** @phpstan-ignore-next-line */
        return $kernel32->GetStdHandle($handle);
    }

    /**
     * Read the current console mode into the given unsigned int.
     */
    protected function queryMode(FFI $kernel32, mixed $handle, FFI\CData $mode): int
    {
        /** @phpstan-ignore-next-line */
        return $kernel32->GetConsoleMode($handle, FFI::addr($mode));
    }

    /**
     * Apply a console mode to the given handle.
     */
    protected function setMode(FFI $kernel32, mixed $handle, int $mode): int
    {
        /** @phpstan-ignore-next-line */
        return $kernel32->SetConsoleMode($handle, $mode);
    }

    /**
     * Read the integer value held by an unsigned int binding.
     */
    protected function modeValue(FFI\CData $mode): int
    {
        /** @phpstan-ignore-next-line */
        return $mode->cdata;
    }

    /**
     * Blocking read of the next wide character code from the console.
     */
    protected function nextCode(FFI $msvcrt): int
    {
        /** @phpstan-ignore-next-line */
        return $msvcrt->_getwch();
    }
}
