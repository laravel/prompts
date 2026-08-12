<?php

use Illuminate\Support\Collection;
use Laravel\Prompts\Prompt;
use Laravel\Prompts\Themes\Default\Concerns\DrawsTabs;
use Laravel\Prompts\Themes\Default\Renderer;

class TestPrompt extends Prompt
{
    public function __construct(
        public Collection $tabs,
        public int $selected = 0,
        public int $width = 60,
    ) {
        static::$themes['default'][static::class] = TestRenderer::class;
    }

    public function value(): mixed
    {
        return null;
    }

    public function display(): void
    {
        static::output()->write($this->renderTheme());
    }
}

class TestRenderer extends Renderer
{
    use DrawsTabs;

    public function __invoke(TestPrompt $prompt)
    {
        return $this->tabs($prompt->tabs, $prompt->selected, $prompt->width);
    }
}

/**
 * Note: Trailing whitespace is intentional in order to match the output.
 * Removing it will cause the test to fail (correctly) while allowing
 * the output to appear indistinguishable from the expected output.
 */

it('renders tabs', function () {
    Prompt::fake();
    
    $tabs = collect(['One', 'Two', 'Three', 'Four', 'Five', 'Six']);

    (new TestPrompt($tabs))->display();

    Prompt::assertStrippedOutputContains(<<<'OUTPUT'
    ╭─────╮                                       
    │ One │  Two    Three    Four    Five    Six  
    ┴─────┴─────────────────────────────────────────────────────
    OUTPUT);
});

it('highlights tabs', function () {
    Prompt::fake();

    $tabs = collect(['One', 'Two', 'Three', 'Four', 'Five', 'Six']);

    (new TestPrompt($tabs, 2))->display();

    Prompt::assertStrippedOutputContains(<<<'OUTPUT'
                  ╭───────╮                       
      One    Two  │ Three │  Four    Five    Six  
    ──────────────┴───────┴─────────────────────────────────────
    OUTPUT);
});

it('truncates tabs', function () {
    Prompt::fake();
    
    $tabs = collect(['One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight']);

    (new TestPrompt($tabs))->display();

    Prompt::assertStrippedOutputContains(<<<'OUTPUT'
    ╭─────╮                                                     
    │ One │  Two    Three    Four    Five    Six    Seven    Eig
    ┴─────┴─────────────────────────────────────────────────────
    OUTPUT);
});

it('scrolls tabs', function () {
    Prompt::fake();

    $tabs = collect(['One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight']);

    (new TestPrompt($tabs, 7))->display();

    Prompt::assertStrippedOutputContains(<<<'OUTPUT'
                                                       ╭───────╮
    e    Two    Three    Four    Five    Six    Seven  │ Eight │
    ───────────────────────────────────────────────────┴───────┴
    OUTPUT);
});
