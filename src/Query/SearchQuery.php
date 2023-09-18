<?php

namespace ATSearchBundle\Query;

use ATSearchBundle\Enum\SearchSourceEnum;
use ATSearchBundle\Query\Filter\LogicalAnd;
use ATSearchBundle\Query\SortClause\SortByField;
use ATSearchBundle\Query\SortClause\SortByRelationField;
use ATSearchBundle\ValueObject\Result;

class SearchQuery
{
    public ?FilterQueryCriterion $filters = null;
    /** @var SortByField[]|SortByRelationField[]|SortQueryCriterion[] */
    public array $sorts = [];

    public int $limit = 10;
    public int $offset = 0;
    public SearchSourceEnum $searchSource = SearchSourceEnum::DOCTRINE;
    public ?Result $result = null;
    public bool $withCount = true;

    public function __construct(public string $targetEntity, public int $tenantId = 0)
    {
    }

    public function and(FilterQueryCriterion $filter): void
    {
        if ($this->filters instanceof LogicalAnd) {
            $this->filters->criteria[] = $filter;
        } elseif ($this->filters !== null) {
            $this->filters = new LogicalAnd([$filter, $this->filters]);
        } else {
            $this->filters = new LogicalAnd([$filter]);
        }
    }
}