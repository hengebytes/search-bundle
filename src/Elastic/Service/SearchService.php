<?php

namespace ATSearchBundle\Elastic\Service;

use ATSearchBundle\Elastic\Converter\InputQueryToElasticFilter;
use ATSearchBundle\Elastic\Converter\InputQueryToElasticSort;
use ATSearchBundle\Elastic\Handler\SearchHandler;
use ATSearchBundle\Elastic\ValueObject\Query;
use ATSearchBundle\Exception\NoConverterException;
use ATSearchBundle\Query\SearchQuery;
use ATSearchBundle\Service\{SearchServiceInterface};
use ATSearchBundle\ValueObject\Result;

readonly class SearchService implements SearchServiceInterface
{
    public function __construct(
        private SearchHandler $searchHandler,
        private InputQueryToElasticFilter $queryToElasticFilter,
        private InputQueryToElasticSort $queryToElasticSort,
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
            $query->filters = $this->queryToElasticFilter->convert($searchQuery->filters);
        }
        $query->sort = $this->queryToElasticSort->convert($searchQuery->sorts);

        return $this->searchHandler->search($query);
    }
}