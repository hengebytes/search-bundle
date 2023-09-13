<?php

namespace ATernovtsii\SearchBundle\Query\Filter;

use ATernovtsii\SearchBundle\Query\FilterQueryCriterion;

readonly class FieldFilter implements FilterQueryCriterion
{
    public string $fieldType;

    public function __construct(
        public string $field,
        public string $operator,
        public mixed $value,
        public string $type = 'column'
    ) {
    }
}