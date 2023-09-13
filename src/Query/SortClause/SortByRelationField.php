<?php

namespace ATernovtsii\SearchBundle\Query\SortClause;

use ATernovtsii\SearchBundle\Query\SortQueryCriterion;

readonly class SortByRelationField implements SortQueryCriterion
{
    public function __construct(public string $field, public string $direction, public string $relationField)
    {
    }
}