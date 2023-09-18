<?php

namespace ATSearchBundle\Elastic\Converter\FilterQuery;

use ATSearchBundle\Elastic\Converter\{FilterInputQueryToElasticConverterInterface, InputQueryToElasticFilter};
use ATSearchBundle\Elastic\ValueObject\QueryDSL\{BoolQuery, RawQuery};
use ATSearchBundle\Exception\NoConverterException;
use ATSearchBundle\Query\Filter\LogicalOr;
use ATSearchBundle\Query\FilterQueryCriterion;

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