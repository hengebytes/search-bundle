<?php

namespace ATernovtsii\SearchBundle\Query\Filter;

use ATernovtsii\SearchBundle\Query\FilterQueryCriterion;

readonly class RelationFieldFilter implements FilterQueryCriterion
{
    public function __construct(
        public array $fields, public string $operator, public mixed $value, public string $type = 'column'
    ) {
    }
}