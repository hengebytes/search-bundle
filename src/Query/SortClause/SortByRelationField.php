<?php

namespace ATSearchBundle\Query\SortClause;

use ATSearchBundle\Query\SortQueryCriterion;

readonly class SortByRelationField implements SortQueryCriterion
{
    public function __construct(public string $field, public string $direction, public string $relationField)
    {
    }
}