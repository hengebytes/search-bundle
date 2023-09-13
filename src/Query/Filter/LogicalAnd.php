<?php

namespace ATernovtsii\SearchBundle\Query\Filter;

use ATernovtsii\SearchBundle\Query\FilterQueryCriterion;

class LogicalAnd implements FilterQueryCriterion
{
    /**
     * @param FilterQueryCriterion[] $criteria
     */
    public function __construct(public array $criteria)
    {
    }
}