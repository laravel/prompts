<?php

use Laravel\Prompts\Key;
use Laravel\Prompts\Terminal;

it('normalizes native terminal keys', function () {
    $terminal = new class([
        'up',
        'down',
        'right',
        'left',
        'enter',
        'backspace',
        'escape',
        'delete',
        'tab',
        'home',
        'end',
        'pageup',
        'pagedown',
        'a',
    ]) extends Terminal
    {
        public function __construct(public array $keys)
        {
            parent::__construct();
        }

        public function hasNativeTerminal(): bool
        {
            return true;
        }

        protected function readNativeKey(): string|false
        {
            return array_shift($this->keys) ?? false;
        }
    };

    expect($terminal->read())->toBe(Key::UP)
        ->and($terminal->read())->toBe(Key::DOWN)
        ->and($terminal->read())->toBe(Key::RIGHT)
        ->and($terminal->read())->toBe(Key::LEFT)
        ->and($terminal->read())->toBe(Key::ENTER)
        ->and($terminal->read())->toBe(Key::BACKSPACE)
        ->and($terminal->read())->toBe(Key::ESCAPE)
        ->and($terminal->read())->toBe(Key::DELETE)
        ->and($terminal->read())->toBe(Key::TAB)
        ->and($terminal->read())->toBe(Key::HOME[0])
        ->and($terminal->read())->toBe(Key::END[0])
        ->and($terminal->read())->toBe(Key::PAGE_UP)
        ->and($terminal->read())->toBe(Key::PAGE_DOWN)
        ->and($terminal->read())->toBe('a')
        ->and($terminal->read())->toBe('');
});

it('uses native terminal dimensions and interactivity checks', function () {
    $terminal = new class extends Terminal
    {
        public function hasNativeTerminal(): bool
        {
            return true;
        }

        protected function nativeStdinIsTty(): bool
        {
            return true;
        }

        protected function nativeSize(): array|false
        {
            return ['columns' => 123, 'rows' => 45];
        }
    };

    expect($terminal->interactive())->toBeTrue()
        ->and($terminal->cols())->toBe(123)
        ->and($terminal->lines())->toBe(45);
});

it('restores native terminal mode once', function () {
    $terminal = new class extends Terminal
    {
        public array $calls = [];

        public function hasNativeTerminal(): bool
        {
            return true;
        }

        protected function enableNativeRawMode(): string|false
        {
            $this->calls[] = 'enable';

            return 'mode-token';
        }

        protected function restoreNativeMode(string $mode): bool
        {
            $this->calls[] = 'restore:'.$mode;

            return true;
        }
    };

    $terminal->setTty('-icanon -isig -echo');
    $terminal->setTty('-icanon -isig -echo');
    $terminal->restoreTty();
    $terminal->restoreTty();

    expect($terminal->calls)->toBe(['enable', 'restore:mode-token']);
});
