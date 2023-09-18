<?php

namespace ATSearchBundle\Query\Filter;

use ATSearchBundle\Query\FilterQueryCriterion;

class LogicalOr implements FilterQueryCriterion
{
    public function __construct(public array $criteria)
    {
    }

}