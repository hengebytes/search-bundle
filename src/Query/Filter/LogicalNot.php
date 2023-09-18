<?php

namespace ATSearchBundle\Query\Filter;

use ATSearchBundle\Query\FilterQueryCriterion;

class LogicalNot implements FilterQueryCriterion
{
    public function __construct(public FilterQueryCriterion $criterion)
    {
    }

}