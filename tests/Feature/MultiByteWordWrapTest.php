<?php

use function Laravel\Prompts\mb_wordwrap;

test('will match wordwrap', function () {
    $str = "This is a story all about how my life got flipped turned upside down and I'd like to take a minute just sit right there I'll tell you how I became the prince of a town called Bel-Air";

    $result = wordwrap($str);

    $mbResult = mb_wordwrap($str);

    expect($mbResult)->toBe($result);
});

test('will match wordwrap on shorter strings', function () {
    $str = "This is a story all\nabout how my life got\nflipped turned upside down and I'd like to take a minute just sit right there I'll tell you how I became the prince of a town called Bel-Air";

    $result = wordwrap($str);

    $mbResult = mb_wordwrap($str);

    expect($mbResult)->toBe($result);
});

test('will match wordwrap on blank lines strings', function () {
    $str = "This is a story all about how my life got flipped turned upside down and I'd\n\nlike to take a minute just sit right there I'll tell you how I became the prince of a town called Bel-Air";

    $result = wordwrap($str);

    $mbResult = mb_wordwrap($str);

    expect($mbResult)->toBe($result);
});

test('will match wordwrap with cut long words enabled', function () {
    $str = "This is a story all about how my life got flippppppppppppppppppppppppped turned upside down and I'd like to take a minute just sit right there I'll tell you how I became the prince of a town called Bel-Air";

    $result = wordwrap($str, 25, "\n", true);

    $mbResult = mb_wordwrap($str, 25, "\n", true);

    expect($mbResult)->toBe($result);
});

test('will match wordwrap with random multiple spaces', function () {
    $str = "     This is a story all about how my life got flipped turned upside down and      I'd      like to take a minute just sit right there I'll tell you how I became the prince of a town called Bel-Air";

    $result = wordwrap($str, 25, "\n", true);

    $mbResult = mb_wordwrap($str, 25, "\n", true);

    expect($mbResult)->toBe($result);
});

test('will match wordwrap with cut long words disabled', function () {
    $str = "This is a story all about how my life got flippppppppppppppppppppppppped turned upside down and I'd like to take a minute just sit right there I'll tell you how I became the prince of a town called Bel-Air";

    $result = wordwrap($str, 25, "\n", false);

    $mbResult = mb_wordwrap($str, 25, "\n", false);

    expect($mbResult)->toBe($result);
});

test('will wrap strings with multi-byte characters', function () {
    $str = "This is a story all about how my life got flippêd turnêd upsidê down and I'd likê to takê a minutê just sit right thêrê I'll têll you how I bêcamê thê princê of a town callêd Bêl-Air";

    $mbResult = mb_wordwrap($str, 18, "\n", false);

    $expectedResult = <<<RESULT
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

test('will wrap strings with emojis', function () {
    $str = "This is a 📖 all about how my life got 🌀 turned upside ⬇️ and I'd like to take a minute just sit right there I'll tell you how I became the prince of a town called Bel-Air";

    $mbResult = mb_wordwrap($str, 13, "\n", false);

    $expectedResult = <<<RESULT
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

test('will wrap strings with emojis and multi-byte characters', function () {
    $str = "This is a 📖 all about how my lifê got 🌀 turnêd upsidê ⬇️ and I'd likê to takê a minutê just sit right thêrê I'll têll you how I bêcamê thê princê of a town callêd Bêl-Air";

    $mbResult = mb_wordwrap($str, 11, "\n", false);

    $expectedResult = <<<RESULT
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

test('will wrap strings with combined emojis', function () {
    $str = "This is a 📖 all about how my life got 🌀 turned upside ⬇️ and I'd like to take a minute just sit right there I'll tell you how I became the prince of a 👨‍👩‍👧‍👦 called Bel-Air";

    $mbResult = mb_wordwrap($str, 13, "\n", false);

    $expectedResult = <<<RESULT
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

test('will handle long strings with multi-byte characters and emojis with cut long words enabled', function () {
    $str = "This is a 📖 all about how my life got 🌀 turned upside ⬇️ and I'd like to take a minute just sit right there I'll tell you how I became the prince of a 👨‍👩‍👧‍👦 called Bel-Air";

    $mbResult = mb_wordwrap($str, 13, "\n", false);

    $expectedResult = <<<RESULT
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
