<?php

namespace ATernovtsii\SearchBundle\Query;

interface SortableEntityInterface
{
    /**
     * @return array<string, boolean>
     */
    public static function getSortableFields(): array;
}