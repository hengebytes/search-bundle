<?php

namespace ATSearchBundle\Search\Service;

use ATSearchBundle\Search\Converter\InputQueryToSearchFilter;
use ATSearchBundle\Search\Converter\InputQueryToSearchSort;
use ATSearchBundle\Search\Handler\SearchHandler;
use ATSearchBundle\Search\ValueObject\Query;
use ATSearchBundle\Exception\NoConverterException;
use ATSearchBundle\Query\SearchQuery;
use ATSearchBundle\Service\{SearchServiceInterface};
use ATSearchBundle\ValueObject\Result;

readonly class SearchService implements SearchServiceInterface
{
    public function __construct(
        private SearchHandler $searchHandler,
        private InputQueryToSearchFilter $queryToSearchFilter,
        private InputQueryToSearchSort $queryToSearchSort,
        private DocumentGenerator $documentGenerator
    ) {
    }

    /**
     * @throws NoConverterException
     */
    public function searchBySearchQuery(SearchQuery $searchQuery): Result
    {
        $query = new Query();
        $query->indexName = $this->documentGenerator->getIndexName($searchQuery->targetEntity);
        $query->tenantId = $searchQuery->tenantId;
        $query->returnSource = true;
        $query->size = $searchQuery->limit;
        $query->from = $searchQuery->offset;
        $query->withCount = $searchQuery->withCount;

        if ($searchQuery->filters) {
            $query->filters = $this->queryToSearchFilter->convert($searchQuery->filters);
        }
        $query->sort = $this->queryToSearchSort->convert($searchQuery->sorts);

        return $this->searchHandler->search($query);
    }
}