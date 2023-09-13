<?php

namespace ATernovtsii\SearchBundle\Elastic\Converter\FilterQuery;

use ATernovtsii\SearchBundle\Elastic\Converter\{FilterInputQueryToElasticConverterInterface, InputQueryToElasticFilter};
use ATernovtsii\SearchBundle\Elastic\ValueObject\QueryDSL\{BoolQuery, RawQuery};
use ATernovtsii\SearchBundle\Exception\NoConverterException;
use ATernovtsii\SearchBundle\Query\Filter\LogicalNot;
use ATernovtsii\SearchBundle\Query\FilterQueryCriterion;

readonly class LogicalNotConverterFilter implements FilterInputQueryToElasticConverterInterface
{
    /**
     * @throws NoConverterException
     */
    public function convert(FilterQueryCriterion $criterion, InputQueryToElasticFilter $converter): array
    {
        if (!$criterion instanceof LogicalNot) {
            throw new \InvalidArgumentException('Unsupported criteria');
        }

        $qb = new BoolQuery();
        $qb->addMustNot(new RawQuery($converter->convert($criterion->criterion)));

        return $qb->toArray();
    }

    public function supports(FilterQueryCriterion $criteria): bool
    {
        return $criteria instanceof LogicalNot;
    }
}