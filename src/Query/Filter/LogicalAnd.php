<?php

namespace ATSearchBundle\Query\Filter;

use ATSearchBundle\Query\FilterQueryCriterion;

class LogicalAnd implements FilterQueryCriterion
{
    /**
     * @param FilterQueryCriterion[] $criteria
     */
    public function __construct(public array $criteria)
    {
    }
}