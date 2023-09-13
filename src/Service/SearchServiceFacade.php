<?php

namespace ATernovtsii\SearchBundle\Service;

use ATernovtsii\SearchBundle\Doctrine\Service\SearchService as DoctrineSearchServiceAlias;
use ATernovtsii\SearchBundle\Elastic\Service\SearchService as ElasticSearchServiceAlias;
use ATernovtsii\SearchBundle\Enum\SearchSourceEnum;
use ATernovtsii\SearchBundle\Query\SearchQuery;
use ATernovtsii\SearchBundle\ValueObject\Result;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;

#[AsAlias(SearchServiceInterface::class)]
readonly class SearchServiceFacade implements SearchServiceInterface
{
    public function __construct(
        private ElasticSearchServiceAlias $elasticSearchService,
        private DoctrineSearchServiceAlias $doctrineSearchService
    ) {
    }

    public function searchBySearchQuery(SearchQuery $searchQuery): Result
    {
        if ($searchQuery->searchSource === SearchSourceEnum::ELASTIC) {
            return $this->elasticSearchService->searchBySearchQuery($searchQuery);
        }

        return $this->doctrineSearchService->searchBySearchQuery($searchQuery);
    }
}