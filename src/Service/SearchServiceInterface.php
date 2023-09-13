<?php

namespace ATernovtsii\SearchBundle\Service;

use ATernovtsii\SearchBundle\Exception\NoConverterException;
use ATernovtsii\SearchBundle\Query\SearchQuery;
use ATernovtsii\SearchBundle\ValueObject\Result;

interface SearchServiceInterface
{
    /**
     * @param SearchQuery $searchQuery
     * @return Result
     * @throws NoConverterException
     */
    public function searchBySearchQuery(SearchQuery $searchQuery): Result;
}