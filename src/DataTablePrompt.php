<?php

namespace Laravel\Prompts;

use Illuminate\Support\Collection;
use Laravel\Prompts\Concerns\TypedValue;
use Laravel\Prompts\KeyBindingsHelp;
use Laravel\Prompts\KeyPressListener;
use Laravel\Prompts\Key;
use Laravel\Prompts\Prompt;
use Throwable;

class DataTable extends Prompt
{
    use TypedValue;

    /**
     * The paginated table headers.
     *
     * @var array<int, string|array<int, string>>
     */
    public array $headers;

    /**
     * The paginated table rows.
     *
     * @var array<int, array<int, string>>
     */
    public array $rows;

    public int $page = 1;

    public int $perPage = 10;

    public int $index = 0;

    public string $query = '';

    public int $totalPages;

    public string $jumpToPage = '';

    /**
     * Create a new PaginatedTable instance.
     *
     * @param  array<int, string|array<int, string>>|Collection<int, string|array<int, string>>  $headers
     * @param  array<int, array<int, string>>|Collection<int, array<int, string>>  $rows
     * @param  array<string, array<callable, string>>  $actions
     *
     * @phpstan-param ($rows is null ? list<list<string>>|Collection<int, list<string>> : list<string|list<string>>|Collection<int, string|list<string>>) $headers
     */
    public function __construct(array|Collection $headers = [], array|Collection|null $rows = null, protected array $actions = [])
    {
        if ($rows === null) {
            $rows = $headers;
            $headers = [];
        }

        $this->required = false;
        $this->validate = null;
        $this->headers = $headers instanceof Collection ? $headers->all() : $headers;
        $this->rows = $rows instanceof Collection ? $rows->all() : $rows;
        $this->totalPages = $this->getTotalPages($rows);
        $this->perPage = $this->calculatePerPage($rows);
    }

    protected function getTotalPages(array $records): int
    {
        return (int) ceil(count($records) / $this->perPage);
    }

    protected function calculatePerPage(array $rows): int
    {
        $highestRow = collect($rows)->map(fn($row) => collect($row)->map(fn($cell) => substr_count($cell, PHP_EOL))->max())->max();
        $terminalHeight = $this->terminal()->lines();
        $availableHeight = $terminalHeight - 10;
        $perPage = floor($availableHeight / ($highestRow + 1));

        return max(1, min($this->perPage, $perPage, count($rows)));
    }

    public function visible(): array
    {
        if ($this->query === '') {
            $this->totalPages = $this->getTotalPages($this->rows);

            return array_slice($this->rows, ($this->page - 1) * $this->perPage, $this->perPage);
        }

        $filtered = array_filter(
            $this->rows,
            fn($row) => str_contains(
                mb_strtolower(implode(' ', $row)),
                mb_strtolower($this->query),
            ),
        );

        $this->totalPages = $this->getTotalPages($filtered);

        $results = array_slice($filtered, 0, $this->perPage);

        if (count($results) > 0) {
            return $results;
        }

        return [];
    }

    public function valueWithCursor(int $maxWidth): string
    {
        return $this->getValueWithCursor($this->query, $maxWidth);
    }

    public function jumpValueWithCursor(int $maxWidth): string
    {
        return $this->getValueWithCursor($this->jumpToPage, $maxWidth);
    }

    protected function getValueWithCursor(string $value, int $maxWidth): string
    {
        if ($value === '') {
            return $this->dim($this->addCursor('', 0, $maxWidth));
        }

        return $this->addCursor($value, $this->cursorPosition, $maxWidth);
    }

    /**
     * Display the table.
     */
    public function display(): bool
    {
        $this->capturePreviousNewLines();

        if (static::shouldFallback()) {
            return false;
        }

        static::$interactive ??= stream_isatty(STDIN);

        if (! static::$interactive) {
            return false;
        }

        try {
            static::terminal()->setTty('-icanon -isig -echo');
        } catch (Throwable $e) {
            static::output()->writeln("<comment>{$e->getMessage()}</comment>");
            static::fallbackWhen(true);

            return false;
        }

        $this->hideCursor();

        $this->browse();
        $this->addDefaultKeyBindings();
        $this->render();

        $this->listener->afterEveryKey(function () {
            $this->addDefaultKeyBindings();
            $this->render();
        })->listenNow();

        return true;
    }

    protected function browse(): void
    {
        $this->state = 'browse';

        $this->listener
            ->clearExisting()
            ->listenForQuit()
            ->onUp(
                fn() => $this->index = max(0, $this->index - 1),
            )
            ->onDown(
                fn() => $this->index = min($this->perPage - 1, count($this->visible()) - 1, $this->index + 1),
            )
            ->onRight(
                function () {
                    $prevPage = $this->page;
                    $this->page = min($this->totalPages, $this->page + 1);

                    if ($prevPage !== $this->page) {
                        $this->index = 0;
                    }
                },
            )
            ->onLeft(
                function () {
                    $prevPage = $this->page;
                    $this->page = max(1, $this->page - 1);

                    if ($prevPage !== $this->page) {
                        $this->index = 0;
                    }
                },
            )
            ->on(Key::ENTER, $this->submit(...))
            ->on('/', $this->search(...))
            ->on('j', $this->jump(...))
            ->listen();
    }

    protected function addCustomKeyBindings(): void
    {
        foreach ($this->actions as $key => [$action, $description]) {
            $this->listener->on($key, fn() => $this->runCustomAction($action));
            $this->keyBindingsHelp->add($key, $description);
        }

        if (! in_array(Key::ENTER, array_keys($this->actions))) {
            $this->listener->on(Key::ENTER, fn() => false);
        }
    }

    protected function addDefaultKeyBindings(): void
    {
        $this->keyBindingsHelp->clear();

        if ($this->state === 'search') {
            $this->keyBindingsHelp->add('Enter', 'Submit');
        } elseif ($this->state === 'jump') {
            $this->keyBindingsHelp->add('Enter', 'Jump to page');
        } else {
            $upArrow = $this->index === 0 ? $this->dim('↑') : '↑';
            $downArrow = $this->index === count($this->visible()) - 1 ? $this->dim('↓') : '↓';
            $leftArrow = $this->page === 1 ? $this->dim('←') : '←';
            $rightArrow = $this->page === $this->totalPages ? $this->dim('→') : '→';

            $this->keyBindingsHelp->add($upArrow . ' ' . $downArrow, 'Row');
            $this->keyBindingsHelp->add($leftArrow . ' ' . $rightArrow, 'Page');
            $this->keyBindingsHelp->add('/', 'Search');
            $this->keyBindingsHelp->add('j', 'Jump to page');
            $this->addCustomKeyBindings();
        }
    }

    protected function search(): void
    {
        $this->state = 'search';
        $this->index = 0;
        $this->page = 1;

        $this->listener
            ->clearExisting()
            ->listenForQuit()
            ->listenToInput($this->query, $this->cursorPosition)
            ->on(
                Key::ENTER,
                function () {
                    if (count($this->visible()) === 0) {
                        return;
                    }

                    $this->browse();
                },
            )
            ->listen();
    }

    protected function jump(): void
    {
        $this->state = 'jump';
        $this->index = 0;

        $this->listener
            ->clearExisting()
            ->listenForQuit()
            ->listenToInput($this->jumpToPage, $this->cursorPosition)
            ->on(
                Key::ENTER,
                function () {
                    if ($this->jumpToPage === '') {
                        $this->browse();

                        return;
                    }

                    if (! is_numeric($this->jumpToPage)) {
                        return;
                    }

                    if ($this->jumpToPage < 1 || $this->jumpToPage > $this->totalPages) {
                        return;
                    }

                    $this->page = (int) $this->jumpToPage;
                    $this->jumpToPage = '';
                    $this->browse();
                },
            )
            ->listen();
    }

    public function runCustomAction(callable $action): bool
    {
        $action($this->visible()[$this->index]);

        return false;
    }

    /**
     * Get the value of the prompt.
     */
    public function value(): bool
    {
        return true;
    }
}
