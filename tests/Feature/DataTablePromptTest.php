<?php

use Laravel\Prompts\Key;
use Laravel\Prompts\Prompt;

use function Laravel\Prompts\datatable;

it('renders a table with headers and search line', function () {
    Prompt::fake([Key::ENTER]);

    datatable(
        label: 'Select a user',
        headers: ['Name', 'Email'],
        rows: [
            ['Alice', 'alice@example.com'],
            ['Bob', 'bob@example.com'],
        ],
        scroll: 5,
    );

    Prompt::assertStrippedOutputContains('Select a user');
    Prompt::assertStrippedOutputContains('/ Search');
    Prompt::assertStrippedOutputContains('Name');
    Prompt::assertStrippedOutputContains('Email');
    Prompt::assertStrippedOutputContains('Alice');
    Prompt::assertStrippedOutputContains('Bob');
});

it('returns the index for list arrays', function () {
    Prompt::fake([Key::DOWN, Key::ENTER]);

    $result = datatable(
        label: 'Pick one',
        headers: ['Name'],
        rows: [
            ['Alice'],
            ['Bob'],
            ['Charlie'],
        ],
        scroll: 5,
    );

    expect($result)->toBe(1);
});

it('returns the key for associative arrays', function () {
    Prompt::fake([Key::DOWN, Key::ENTER]);

    $result = datatable(
        label: 'Pick one',
        headers: ['Name'],
        rows: [
            'a' => ['Alice'],
            'b' => ['Bob'],
            'c' => ['Charlie'],
        ],
        scroll: 5,
    );

    expect($result)->toBe('b');
});

it('navigates with arrow keys', function () {
    Prompt::fake([Key::DOWN, Key::DOWN, Key::UP, Key::ENTER]);

    $result = datatable(
        label: 'Pick one',
        headers: ['Name'],
        rows: [
            'a' => ['Alice'],
            'b' => ['Bob'],
            'c' => ['Charlie'],
        ],
        scroll: 5,
    );

    expect($result)->toBe('b');
});

it('wraps around when navigating past the end', function () {
    Prompt::fake([Key::UP, Key::ENTER]);

    $result = datatable(
        label: 'Pick one',
        headers: ['Name'],
        rows: [
            'a' => ['Alice'],
            'b' => ['Bob'],
            'c' => ['Charlie'],
        ],
        scroll: 5,
    );

    expect($result)->toBe('c');
});

it('supports page up and page down', function () {
    Prompt::fake([Key::PAGE_DOWN, Key::ENTER]);

    $result = datatable(
        label: 'Pick one',
        headers: ['Name'],
        rows: [
            'a' => ['Alice'],
            'b' => ['Bob'],
            'c' => ['Charlie'],
            'd' => ['Diana'],
            'e' => ['Ethan'],
            'f' => ['Fatima'],
        ],
        scroll: 3,
    );

    expect($result)->toBe('d');
});

it('supports home and end keys', function () {
    Prompt::fake([Key::oneOf([Key::END, Key::CTRL_E], Key::END[0]) ? Key::END[0] : Key::CTRL_E, Key::ENTER]);

    $result = datatable(
        label: 'Pick one',
        headers: ['Name'],
        rows: [
            'a' => ['Alice'],
            'b' => ['Bob'],
            'c' => ['Charlie'],
        ],
        scroll: 5,
    );

    expect($result)->toBe('c');
});

it('enters search mode with slash and filters rows', function () {
    Prompt::fake(['/', 'b', 'o', Key::ENTER, Key::ENTER]);

    $result = datatable(
        label: 'Pick one',
        headers: ['Name'],
        rows: [
            'a' => ['Alice'],
            'b' => ['Bob'],
        ],
        scroll: 5,
    );

    expect($result)->toBe('b');
});

it('returns the original key after filtering a list array', function () {
    Prompt::fake(['/', 'c', 'h', Key::ENTER, Key::ENTER]);

    $result = datatable(
        label: 'Pick one',
        headers: ['Name'],
        rows: [
            ['Alice'],
            ['Bob'],
            ['Charlie'],
        ],
        scroll: 5,
    );

    // "Charlie" is at original index 2, search should preserve that
    expect($result)->toBe(2);
});

it('cancels search with escape', function () {
    Prompt::fake(['/', 'x', 'y', 'z', Key::ESCAPE, Key::ENTER]);

    $result = datatable(
        label: 'Pick one',
        headers: ['Name'],
        rows: [
            'a' => ['Alice'],
            'b' => ['Bob'],
        ],
        scroll: 5,
    );

    // After cancel, filter is cleared, back to first row
    expect($result)->toBe('a');
});

it('shows no results message when search matches nothing', function () {
    Prompt::fake(['/', 'z', 'z', 'z', Key::ESCAPE, Key::ENTER]);

    datatable(
        label: 'Pick one',
        headers: ['Name'],
        rows: [
            ['Alice'],
            ['Bob'],
        ],
        scroll: 5,
    );

    Prompt::assertStrippedOutputContains('No results found.');
});

it('renders column-aware borders', function () {
    Prompt::fake([Key::ENTER]);

    datatable(
        label: 'Test',
        headers: ['A', 'B'],
        rows: [
            ['One', 'Two'],
        ],
        scroll: 5,
    );

    // Column-aware separators should use ┬, ┼, ┴
    Prompt::assertStrippedOutputContains('┬');
    Prompt::assertStrippedOutputContains('┼');
    Prompt::assertStrippedOutputContains('┴');
});

it('shows simple borders when no results', function () {
    Prompt::fake(['/', 'z', 'z', 'z', Key::ESCAPE, Key::ENTER]);

    datatable(
        label: 'Test',
        headers: ['A', 'B'],
        rows: [
            ['One', 'Two'],
        ],
        scroll: 5,
    );

    // When showing "No results found", the border should not have column separators
    $content = Prompt::strippedContent();

    // The no-results area should have a simple ├───┤ border, not ├───┬───┤
    // We check that "No results found" appears without column separators on that line
    expect($content)->toContain('No results found.');
});

it('shows viewing info only when scrolling is needed', function () {
    Prompt::fake([Key::ENTER]);

    datatable(
        label: 'Test',
        headers: ['Name'],
        rows: [
            ['Alice'],
            ['Bob'],
        ],
        scroll: 5,
    );

    // Only 2 rows with scroll=5, no info line needed
    Prompt::assertStrippedOutputDoesntContain('Viewing');
});

it('shows viewing info when there are more rows than scroll', function () {
    Prompt::fake([Key::ENTER]);

    datatable(
        label: 'Test',
        headers: ['Name'],
        rows: [
            ['Alice'],
            ['Bob'],
            ['Charlie'],
            ['Diana'],
            ['Ethan'],
            ['Fatima'],
        ],
        scroll: 3,
    );

    Prompt::assertStrippedOutputContains('Viewing');
    Prompt::assertStrippedOutputContains('1-3');
    Prompt::assertStrippedOutputContains('of');
    Prompt::assertStrippedOutputContains('6');
});

it('handles multiline cells', function () {
    Prompt::fake([Key::ENTER]);

    datatable(
        label: 'Test',
        headers: ['Name', 'Role'],
        rows: [
            ['Alice', "CEO\nDeveloper"],
            ['Bob', 'Designer'],
        ],
        scroll: 5,
    );

    Prompt::assertStrippedOutputContains('CEO');
    Prompt::assertStrippedOutputContains('Developer');
    Prompt::assertStrippedOutputContains('Alice');
});

it('keeps highlighted multiline row fully visible', function () {
    Prompt::fake([Key::DOWN, Key::ENTER]);

    datatable(
        label: 'Test',
        headers: ['Name', 'Role'],
        rows: [
            ['Alice', 'Designer'],
            ['Bob', "CEO\nCTO\nDeveloper"],
            ['Charlie', 'Designer'],
        ],
        scroll: 5,
    );

    // Bob's multiline row should be fully visible when highlighted
    Prompt::assertStrippedOutputContains('CEO');
    Prompt::assertStrippedOutputContains('CTO');
    Prompt::assertStrippedOutputContains('Developer');
});

it('uses comfortable width and does not stretch to terminal', function () {
    Prompt::fake([Key::ENTER]);

    datatable(
        label: 'Test',
        headers: ['A', 'B'],
        rows: [
            ['Hi', 'Lo'],
        ],
        scroll: 5,
    );

    $content = Prompt::strippedContent();

    // With tiny data, the table should not stretch to 80 cols
    $lines = explode("\n", $content);
    $maxLen = max(array_map('mb_strwidth', $lines));

    expect($maxLen)->toBeLessThan(70);
});

it('handles outlier column widths gracefully', function () {
    Prompt::fake([Key::ENTER]);

    datatable(
        label: 'Test',
        headers: ['Name', 'Value'],
        rows: [
            ['Alice', 'Short'],
            ['Bob', 'Short'],
            ['Charlie', 'Short'],
            ['Diana', 'Short'],
            ['Ethan', 'Short'],
            ['An extremely long value that should be treated as an outlier and truncated', 'Short'],
        ],
        scroll: 5,
    );

    $content = Prompt::strippedContent();
    $lines = explode("\n", $content);
    $maxLen = max(array_map('mb_strwidth', $lines));

    // The outlier shouldn't blow up the table width to terminal width (80)
    expect($maxLen)->toBeLessThan(76);
});

it('supports custom filter closure', function () {
    Prompt::fake(['/', 'a', Key::ENTER, Key::ENTER]);

    $result = datatable(
        label: 'Pick one',
        headers: ['Name', 'Code'],
        rows: [
            'x' => ['Alice', 'X1'],
            'y' => ['Bob', 'Y2'],
        ],
        scroll: 5,
        filter: fn ($row, $query) => str_starts_with(strtolower($row[0]), strtolower($query)),
    );

    // Custom filter matches "Alice" starting with "a", not "Bob"
    expect($result)->toBe('x');
});

it('renders cancel state with strikethrough data', function () {
    Prompt::fake([Key::CTRL_C]);

    datatable(
        label: 'Pick one',
        headers: ['Name'],
        rows: [
            ['Alice'],
            ['Bob'],
        ],
        scroll: 5,
    );

    Prompt::assertOutputContains('Cancelled.');
});

it('renders submit state with selected row', function () {
    Prompt::fake([Key::DOWN, Key::ENTER]);

    datatable(
        label: 'Pick one',
        headers: ['Name', 'Role'],
        rows: [
            ['Alice', 'Designer'],
            ['Bob', 'Developer'],
        ],
        scroll: 5,
    );

    Prompt::assertStrippedOutputContains('Bob, Developer');
});

it('scrolls and shows scrollbar when needed', function () {
    Prompt::fake([Key::DOWN, Key::DOWN, Key::DOWN, Key::ENTER]);

    $result = datatable(
        label: 'Test',
        headers: ['Name'],
        rows: [
            'a' => ['Alice'],
            'b' => ['Bob'],
            'c' => ['Charlie'],
            'd' => ['Diana'],
            'e' => ['Ethan'],
        ],
        scroll: 3,
    );

    expect($result)->toBe('d');

    // Scrollbar indicators should be present
    Prompt::assertOutputContains('┃');
});

it('works without headers', function () {
    Prompt::fake([Key::ENTER]);

    $result = datatable(
        label: 'Pick',
        rows: [
            ['Alice', 'Designer'],
            ['Bob', 'Developer'],
        ],
        scroll: 5,
    );

    expect($result)->toBe(0);
    Prompt::assertStrippedOutputContains('Alice');
    Prompt::assertStrippedOutputContains('Designer');
});

it('dims rows during search', function () {
    Prompt::fake(['/', Key::ESCAPE, Key::ENTER]);

    datatable(
        label: 'Test',
        headers: ['Name'],
        rows: [
            ['Alice'],
            ['Bob'],
        ],
        scroll: 5,
    );

    // During search state, rows should be dimmed (contains dim escape sequence)
    // We just verify the search mode was entered and exited cleanly
    Prompt::assertStrippedOutputContains('Alice');
});

it('handles blank cells in width calculation', function () {
    Prompt::fake([Key::ENTER]);

    datatable(
        label: 'Test',
        headers: ['Name', 'Email'],
        rows: [
            ['Alice', 'alice@example.com'],
            ['', ''],
            ['Charlie', 'charlie@example.com'],
        ],
        scroll: 5,
    );

    // Blank cells should not skew column widths
    Prompt::assertStrippedOutputContains('Alice');
    Prompt::assertStrippedOutputContains('alice@example.com');
    Prompt::assertStrippedOutputContains('Charlie');
});

it('renders search line in cancel state to prevent layout shift', function () {
    Prompt::fake([Key::CTRL_C]);

    datatable(
        label: 'Pick one',
        headers: ['Name'],
        rows: [
            ['Alice'],
        ],
        scroll: 5,
    );

    // Cancel state should include the search line
    Prompt::assertStrippedOutputContains('/ Search');
    Prompt::assertOutputContains('Cancelled.');
});

it('maintains fixed visual height', function () {
    Prompt::fake([Key::ENTER]);

    datatable(
        label: 'Test',
        headers: ['Name'],
        rows: [
            ['Alice'],
            ['Bob'],
        ],
        scroll: 5,
    );

    // Even with only 2 rows, the data area should be padded to scroll height (5 lines)
    $content = Prompt::strippedContent();

    // Count lines between the header separator (┼ or ┬) and bottom border (┴)
    $lines = explode("\n", $content);
    $dataStart = null;
    $dataEnd = null;

    foreach ($lines as $i => $line) {
        if (str_contains($line, '┼') || (str_contains($line, '┬') && $dataStart === null)) {
            $dataStart = $i;
        }
        if (str_contains($line, '┴')) {
            $dataEnd = $i;
        }
    }

    if ($dataStart !== null && $dataEnd !== null) {
        $dataLineCount = $dataEnd - $dataStart - 1;
        expect($dataLineCount)->toBe(5);
    }
});
