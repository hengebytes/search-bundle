<?php

namespace ATSearchBundle\Service;

use ATSearchBundle\Exception\NoConverterException;
use ATSearchBundle\Query\SearchQuery;
use ATSearchBundle\ValueObject\Result;

interface SearchServiceInterface
{
    /**
     * @param SearchQuery $searchQuery
     * @return Result
     * @throws NoConverterException
     */
    public function searchBySearchQuery(SearchQuery $searchQuery): Result;
}