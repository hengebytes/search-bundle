<?php

namespace ATernovtsii\SearchBundle\Elastic\Converter\FilterQuery;

use ATernovtsii\SearchBundle\Elastic\Converter\{FilterInputQueryToElasticConverterInterface, InputQueryToElasticFilter};
use ATernovtsii\SearchBundle\Elastic\ValueObject\QueryDSL\{BoolQuery, RawQuery};
use ATernovtsii\SearchBundle\Exception\NoConverterException;
use ATernovtsii\SearchBundle\Query\Filter\LogicalOr;
use ATernovtsii\SearchBundle\Query\FilterQueryCriterion;

readonly class LogicalOrConverterFilter implements FilterInputQueryToElasticConverterInterface
{
    /**
     * @throws NoConverterException
     */
    public function convert(FilterQueryCriterion $criterion, InputQueryToElasticFilter $converter): array
    {
        if (!$criterion instanceof LogicalOr) {
            throw new \InvalidArgumentException('Unsupported criteria');
        }
        if (!$criterion->criteria) {
            throw new \RuntimeException('Invalid criteria in LogicalOr criterion.');
        }

        $qb = new BoolQuery();
        foreach ($criterion->criteria as $item) {
            $qb->addShould(new RawQuery($converter->convert($item)));
        }

        return $qb->toArray();
    }

    public function supports(FilterQueryCriterion $criteria): bool
    {
        return $criteria instanceof LogicalOr;
    }
}