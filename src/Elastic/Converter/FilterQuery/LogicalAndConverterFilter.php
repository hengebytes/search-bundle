<?php

namespace ATernovtsii\SearchBundle\Elastic\Converter\FilterQuery;

use ATernovtsii\SearchBundle\Elastic\Converter\{FilterInputQueryToElasticConverterInterface, InputQueryToElasticFilter};
use ATernovtsii\SearchBundle\Elastic\ValueObject\QueryDSL\{BoolQuery, RawQuery};
use ATernovtsii\SearchBundle\Query\Filter\LogicalAnd;
use ATernovtsii\SearchBundle\Query\FilterQueryCriterion;

readonly class LogicalAndConverterFilter implements FilterInputQueryToElasticConverterInterface
{
    public function convert(FilterQueryCriterion $criterion, InputQueryToElasticFilter $converter): array
    {
        if (!$criterion instanceof LogicalAnd) {
            throw new \InvalidArgumentException('Unsupported criteria');
        }
        if (!$criterion->criteria) {
            throw new \RuntimeException('Invalid criteria in LogicalAnd criterion.');
        }

        $qb = new BoolQuery();
        foreach ($criterion->criteria as $item) {
            $qb->addMust(new RawQuery($converter->convert($item)));
        }

        return $qb->toArray();
    }

    public function supports(FilterQueryCriterion $criteria): bool
    {
        return $criteria instanceof LogicalAnd;
    }
}