<?php

namespace ATSearchBundle\Service;

use ATSearchBundle\Doctrine\Service\SearchService as DoctrineSearchServiceAlias;
use ATSearchBundle\Search\Service\SearchService as ElasticOpenSearchSearchServiceAlias;
use ATSearchBundle\Enum\SearchSourceEnum;
use ATSearchBundle\Query\SearchQuery;
use ATSearchBundle\ValueObject\Result;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;

#[AsAlias(SearchServiceInterface::class)]
readonly class SearchServiceFacade implements SearchServiceInterface
{
    public function __construct(
        private ElasticOpensearchSearchServiceAlias $searchService,
        private DoctrineSearchServiceAlias $doctrineSearchService
    ) {
    }

    public function searchBySearchQuery(SearchQuery $searchQuery): Result
    {
        if ($searchQuery->searchSource === SearchSourceEnum::SEARCH) {
            return $this->searchService->searchBySearchQuery($searchQuery);
        }

        return $this->doctrineSearchService->searchBySearchQuery($searchQuery);
    }
}