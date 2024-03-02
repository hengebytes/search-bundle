<?php

namespace ATSearchBundle\Exception;

use ATSearchBundle\Query\Filter\CustomFieldFilter;
use ATSearchBundle\Query\Filter\FieldFilter;
use ATSearchBundle\Query\Filter\RelationFieldFilter;
use ATSearchBundle\Query\FilterQueryCriterion;
use ATSearchBundle\Query\SortClause\SortByField;
use ATSearchBundle\Query\SortClause\SortByRelationField;
use ATSearchBundle\Query\SortQueryCriterion;

class CriterionFieldNotIndexedException extends \InvalidArgumentException
{
    public function __construct(FilterQueryCriterion|SortQueryCriterion $criterion)
    {
        $reason = match (true) {
            $criterion instanceof SortByRelationField => $criterion->relationField . '.' . $criterion->field,
            $criterion instanceof SortByField => $criterion->field,
            $criterion instanceof RelationFieldFilter => implode('.', $criterion->fields),
            $criterion instanceof CustomFieldFilter => $criterion->field,
            $criterion instanceof FieldFilter => $criterion->field,
            default => get_class($criterion),
        };
        parent::__construct('Unsupported criteria. Field is not indexed: ' . $reason);
    }
}