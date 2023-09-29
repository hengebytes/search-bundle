<?php

namespace ATSearchBundle\Doctrine\Service;

use ATSearchBundle\Doctrine\Converter\QueryToRepositoryQuery;
use ATSearchBundle\Query\SearchQuery;
use ATSearchBundle\Service\SearchServiceInterface;
use ATSearchBundle\ValueObject\Result;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

readonly class SearchService implements SearchServiceInterface
{
    public function __construct(
        private QueryToRepositoryQuery $queryConverter, private EntityManagerInterface $em
    ) {
    }

    public function searchBySearchQuery(SearchQuery $searchQuery): Result
    {
        $repo = $this->em->getRepository($searchQuery->targetEntity);

        $qb = $repo->createQueryBuilder($this->getAlias($searchQuery->targetEntity));

        if ($searchQuery->limit === 0) {
            $doctrineQuery = $this->queryConverter->getDoctrineQueryForCount($searchQuery, $qb);
            try {
                $totalCount = (int)$doctrineQuery->getSingleScalarResult();
            } catch (NonUniqueResultException|NoResultException) {
                $totalCount = 0;
            }

            return new Result($totalCount, []);
        }

        $doctrineQuery = $this->queryConverter->getDoctrineQuery($searchQuery, $qb);
        $result = $doctrineQuery->getResult();

        if ($searchQuery->withCount) {
            $totalCount = $this->getTotalCountFunction($searchQuery);
        } else {
            $totalCount = 0;
        }

        return new Result($totalCount, $result);
    }

    private function getTotalCountFunction($query): callable
    {
        $countQuery = clone $query;
        $countQuery->limit = 0;

        return fn() => $this->searchBySearchQuery($countQuery)->getTotalCount();
    }

    private function getAlias($entity): string
    {
        return strtolower(
            substr(substr($entity, strrpos($entity, '\\') + 1), 0, 1)
        );
    }
}