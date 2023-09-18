<?php

namespace ATSearchBundle\Exception;

use Exception;
use ATSearchBundle\Query\FilterQueryCriterion;
use ATSearchBundle\Query\SortQueryCriterion;

class NoConverterException extends Exception
{
    public function __construct(FilterQueryCriterion|SortQueryCriterion $criteria)
    {
        parent::__construct('No converter found for criteria: ' . get_class($criteria));
    }
}