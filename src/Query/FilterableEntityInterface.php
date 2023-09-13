<?php

namespace ATernovtsii\SearchBundle\Query;

interface FilterableEntityInterface
{
    /**
     * @return array<string, boolean>
     */
    public static function getFilterableFields(): array;
}