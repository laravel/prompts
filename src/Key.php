<?php

namespace Laravel\Prompts;

class Key
{
    const UP = "\e[A";

    const DOWN = "\e[B";

    const RIGHT = "\e[C";

    const LEFT = "\e[D";

    const DELETE = "\e[3~";

    const BACKSPACE = "\177";

    const ENTER = "\n";

    const SPACE = ' ';

    const TAB = "\t";

    const CTRL_C = "\x03";
}
