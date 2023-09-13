<?php

namespace ATernovtsii\SearchBundle\Doctrine;

use Doctrine\ORM\QueryBuilder;

class JoinAwareQueryBuilder
{
    private int $setCounter = 1;

    public function __construct(
        public QueryBuilder $queryBuilder,
        public array $joins = [],
        public string $rootAlias = 'root',
    ) {
    }

    public function getNewParamKey($key = 'k'): string
    {
        return $key . ++$this->setCounter;
    }
}