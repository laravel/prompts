<?php

namespace Laravel\Prompts\Themes\Default\Concerns;

trait DrawsTrees
{
    /**
     * Render a tree of nested items as connector-prefixed lines.
     *
     * @param  array<int|string, mixed>  $items
     * @return array<int, string>
     */
    protected function treeLines(array $items, int $width, string $prefix = ''): array
    {
        $lines = [];
        $nodes = $this->treeNodes($items);
        $lastIndex = count($nodes) - 1;

        foreach ($nodes as $index => $node) {
            $isLast = $index === $lastIndex;
            $connector = $isLast ? '└─ ' : '├─ ';
            $childPrefix = $prefix.($isLast ? '   ' : $this->dim('│').'  ');

            $labelWidth = max(1, $width - mb_strwidth($this->stripEscapeSequences($prefix)) - mb_strwidth($connector));

            foreach ($this->ansiWordwrap($this->autoFormat($node['label']), $labelWidth) as $i => $line) {
                $lines[] = ($i === 0 ? $prefix.$this->dim($connector) : $childPrefix).$line;
            }

            $lines = array_merge($lines, $this->treeLines($node['children'], $width, $childPrefix));
        }

        return $lines;
    }

    /**
     * Normalize one level of items into labeled nodes with raw children.
     *
     * @param  array<int|string, mixed>  $items
     * @return array<int, array{label: string, children: array<int|string, mixed>}>
     */
    protected function treeNodes(array $items): array
    {
        $nodes = [];

        foreach ($items as $key => $value) {
            if (is_string($key)) {
                $nodes[] = ['label' => $key, 'children' => is_array($value) ? $value : [$value]];

                continue;
            }

            if (is_array($value)) {
                $nodes = array_merge($nodes, $this->treeNodes($value));

                continue;
            }

            $nodes[] = ['label' => (string) $value, 'children' => []];
        }

        return $nodes;
    }
}
