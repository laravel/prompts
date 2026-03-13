<?php

use Laravel\Prompts\Themes\Default\Concerns\InteractsWithStrings;

$instance = new class
{
    use InteractsWithStrings;

    public function wordwrap(...$args)
    {
        return $this->mbWordwrap(...$args);
    }
};

test('will match wordwrap', function () use ($instance) {
    $str = "This is a story all about how my life got flipped turned upside down and I'd like to take a minute just sit right there I'll tell you how I became the prince of a town called Bel-Air";

    $result = wordwrap($str);

    $mbResult = $instance->wordwrap($str);

    expect($mbResult)->toBe($result);
});

test('will match wordwrap on shorter strings', function () use ($instance) {
    $str = "This is a story all\nabout how my life got\nflipped turned upside down and I'd like to take a minute just sit right there I'll tell you how I became the prince of a town called Bel-Air";

    $result = wordwrap($str);

    $mbResult = $instance->wordwrap($str);

    expect($mbResult)->toBe($result);
});

test('will match wordwrap on blank lines strings', function () use ($instance) {
    $str = "This is a story all about how my life got flipped turned upside down and I'd\n\nlike to take a minute just sit right there I'll tell you how I became the prince of a town called Bel-Air";

    $result = wordwrap($str);

    $mbResult = $instance->wordwrap($str);

    expect($mbResult)->toBe($result);
});

test('will match wordwrap with cut long words enabled', function () use ($instance) {
    $str = "This is a story all about how my life got flippppppppppppppppppppppppped turned upside down and I'd like to take a minute just sit right there I'll tell you how I became the prince of a town called Bel-Air";

    $result = wordwrap($str, 25, "\n", true);

    $mbResult = $instance->wordwrap($str, 25, "\n", true);

    expect($mbResult)->toBe($result);
});

test('will match wordwrap with random multiple spaces', function () use ($instance) {
    $str = "     This is a story all about how my life got flipped turned upside down and      I'd      like to take a minute just sit right there I'll tell you how I became the prince of a town called Bel-Air";

    $result = wordwrap($str, 25, "\n", true);

    $mbResult = $instance->wordwrap($str, 25, "\n", true);

    expect($mbResult)->toBe($result);
});

test('will match wordwrap with cut long words disabled', function () use ($instance) {
    $str = "This is a story all about how my life got flippppppppppppppppppppppppped turned upside down and I'd like to take a minute just sit right there I'll tell you how I became the prince of a town called Bel-Air";

    $result = wordwrap($str, 25, "\n", false);

    $mbResult = $instance->wordwrap($str, 25, "\n", false);

    expect($mbResult)->toBe($result);
});

test('will wrap strings with multi-byte characters', function () use ($instance) {
    $str = "This is a story all about how my life got flippêd turnêd upsidê down and I'd likê to takê a minutê just sit right thêrê I'll têll you how I bêcamê thê princê of a town callêd Bêl-Air";

    $mbResult = $instance->wordwrap($str, 18, "\n", false);

    $expectedResult = <<<'RESULT'
    This is a story
    all about how my
    life got flippêd
    turnêd upsidê down
    and I'd likê to
    takê a minutê just
    sit right thêrê
    I'll têll you how
    I bêcamê thê
    princê of a town
    callêd Bêl-Air
    RESULT;

    expect($mbResult)->toBe($expectedResult);
});

test('will wrap strings with emojis', function () use ($instance) {
    $str = "This is a 📖 all about how my life got 🌀 turned upside ⬇️ and I'd like to take a minute just sit right there I'll tell you how I became the prince of a town called Bel-Air";

    $mbResult = $instance->wordwrap($str, 13, "\n", false);

    $expectedResult = <<<'RESULT'
    This is a 📖
    all about how
    my life got
    🌀 turned
    upside ⬇️ and
    I'd like to
    take a minute
    just sit
    right there
    I'll tell you
    how I became
    the prince of
    a town called
    Bel-Air
    RESULT;

    expect($mbResult)->toBe($expectedResult);
});

test('will wrap strings with emojis and multi-byte characters', function () use ($instance) {
    $str = "This is a 📖 all about how my lifê got 🌀 turnêd upsidê ⬇️ and I'd likê to takê a minutê just sit right thêrê I'll têll you how I bêcamê thê princê of a town callêd Bêl-Air";

    $mbResult = $instance->wordwrap($str, 11, "\n", false);

    $expectedResult = <<<'RESULT'
    This is a
    📖 all
    about how
    my lifê got
    🌀 turnêd
    upsidê ⬇️
    and I'd
    likê to
    takê a
    minutê just
    sit right
    thêrê I'll
    têll you
    how I
    bêcamê thê
    princê of a
    town callêd
    Bêl-Air
    RESULT;

    expect($mbResult)->toBe($expectedResult);
});

test('will wrap strings with combined emojis', function () use ($instance) {
    $str = "This is a 📖 all about how my life got 🌀 turned upside ⬇️ and I'd like to take a minute just sit right there I'll tell you how I became the prince of a 👨‍👩‍👧‍👦 called Bel-Air";

    $mbResult = $instance->wordwrap($str, 13, "\n", false);

    $expectedResult = <<<'RESULT'
    This is a 📖
    all about how
    my life got
    🌀 turned
    upside ⬇️ and
    I'd like to
    take a minute
    just sit
    right there
    I'll tell you
    how I became
    the prince of
    a 👨‍👩‍👧‍👦 called
    Bel-Air
    RESULT;

    expect($mbResult)->toBe($expectedResult);
});

test('will handle long strings with multi-byte characters and emojis with cut long words enabled', function () use ($instance) {
    $str = "This is a 📖 all about how my life got 🌀 turned upside ⬇️ and I'd like to take a minute just sit right there I'll tell you how I became the prince of a 👨‍👩‍👧‍👦 called Bel-Air";

    $mbResult = $instance->wordwrap($str, 13, "\n", false);

    $expectedResult = <<<'RESULT'
    This is a 📖
    all about how
    my life got
    🌀 turned
    upside ⬇️ and
    I'd like to
    take a minute
    just sit
    right there
    I'll tell you
    how I became
    the prince of
    a 👨‍👩‍👧‍👦 called
    Bel-Air
    RESULT;

    expect($mbResult)->toBe($expectedResult);
});
