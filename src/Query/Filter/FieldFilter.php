<?php

namespace ATSearchBundle\Query\Filter;

use ATSearchBundle\Query\FilterQueryCriterion;

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