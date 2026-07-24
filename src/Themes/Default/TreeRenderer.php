<?php

namespace Laravel\Prompts\Themes\Default;

use Laravel\Prompts\Tree;

class TreeRenderer extends Renderer
{
    use Concerns\DrawsTrees;
    use Concerns\InteractsWithStrings;

    protected int $minWidth = 60;

    /**
     * Render the tree.
     */
    public function __invoke(Tree $tree): string
    {
        foreach ($this->treeLines($tree->items, $tree->terminal()->cols() - 2) as $line) {
            $this->line(" {$line}");
        }

        return $this;
    }
}
