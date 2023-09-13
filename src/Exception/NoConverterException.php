<?php

namespace ATernovtsii\SearchBundle\Exception;

use Exception;
use ATernovtsii\SearchBundle\Query\FilterQueryCriterion;
use ATernovtsii\SearchBundle\Query\SortQueryCriterion;

class NoConverterException extends Exception
{
    public function __construct(FilterQueryCriterion|SortQueryCriterion $criteria)
    {
        parent::__construct('No converter found for criteria: ' . get_class($criteria));
    }
}