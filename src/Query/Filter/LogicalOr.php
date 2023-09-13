<?php

namespace ATernovtsii\SearchBundle\Query\Filter;

use ATernovtsii\SearchBundle\Query\FilterQueryCriterion;

class LogicalOr implements FilterQueryCriterion
{
    public function __construct(public array $criteria)
    {
    }

}