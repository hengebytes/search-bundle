<?php

namespace ATernovtsii\SearchBundle\Elastic\Service;

use ATernovtsii\SearchBundle\Elastic\Converter\InputQueryToElasticFilter;
use ATernovtsii\SearchBundle\Elastic\Converter\InputQueryToElasticSort;
use ATernovtsii\SearchBundle\Elastic\Handler\SearchHandler;
use ATernovtsii\SearchBundle\Elastic\ValueObject\Query;
use ATernovtsii\SearchBundle\Exception\NoConverterException;
use ATernovtsii\SearchBundle\Query\SearchQuery;
use ATernovtsii\SearchBundle\Service\{SearchServiceInterface};
use ATernovtsii\SearchBundle\ValueObject\Result;

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