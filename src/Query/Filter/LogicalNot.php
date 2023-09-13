<?php

namespace ATernovtsii\SearchBundle\Query\Filter;

use ATernovtsii\SearchBundle\Query\FilterQueryCriterion;

class LogicalNot implements FilterQueryCriterion
{
    public function __construct(public FilterQueryCriterion $criterion)
    {
    }

}