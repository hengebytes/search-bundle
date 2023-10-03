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
    /**
     * Whether to include count of total results in the result (count function in Doctrine)
     * @var bool
     */
    public bool $withCount = true;
    /**
     * Doctrine entity ID field name
     * Used for group by in searches
     * @var string
     */
    public string $idField = 'id';
    /**
     * Additional selects to be added to the query for Doctrine
     * Fields to be retrieved from Search engine
     * @var array
     */
    public array $subSelects = [];

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