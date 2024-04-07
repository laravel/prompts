<?php

namespace Laravel\Prompts\Themes\Default\Concerns;

Trait Number
{
    /**
     * Determine whether the values are consecutive integers.
     * 
     * @param   array<int|string, int>  $values
     * @return  bool
     */
    protected function isConsecutive(array $values): bool
    {
        return count($values) > 0 && array_filter($values, 'is_int') === $values
        ? range(
            array_slice($values, 0, 1)[0],
            array_slice($values, 0, 1)[0] + count($values) - 1
        ) === array_values($values)
        : false;
    }
}
