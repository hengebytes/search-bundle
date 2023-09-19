<?php

namespace ATSearchBundle\Search\Converter\FilterQuery;

use ATSearchBundle\Search\Converter\{FilterInputQueryToElasticConverterInterface, InputQueryToElasticFilter};
use ATSearchBundle\Search\ValueObject\QueryDSL\{BoolQuery, RawQuery};
use ATSearchBundle\Exception\NoConverterException;
use ATSearchBundle\Query\Filter\LogicalNot;
use ATSearchBundle\Query\FilterQueryCriterion;

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