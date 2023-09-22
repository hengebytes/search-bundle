<?php

namespace ATSearchBundle\Search\Converter\FilterQuery;

use ATSearchBundle\Search\Converter\{FilterInputQueryToSearchQueryConverterInterface, InputQueryToSearchFilter};
use ATSearchBundle\Search\ValueObject\QueryDSL\{BoolQuery, RawQuery};
use ATSearchBundle\Query\Filter\LogicalAnd;
use ATSearchBundle\Query\FilterQueryCriterion;

readonly class LogicalAndConverterFilter implements FilterInputQueryToSearchQueryConverterInterface
{
    public function convert(FilterQueryCriterion $criterion, InputQueryToSearchFilter $converter): array
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