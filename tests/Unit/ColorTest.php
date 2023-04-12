<?php

class Colors
{
    use Laravel\Prompts\Concerns\Colors;
}

it('formats', function () {
    $colors = new Colors;

    $result = $colors->format('normal <fg=gray>gray</> normal <fg=red>red</> normal');

    expect($result)->toBe("normal \e[90mgray\e[39m normal \e[31mred\e[39m normal");
});
