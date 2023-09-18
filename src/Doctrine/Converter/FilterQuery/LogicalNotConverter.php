<?php

namespace ATSearchBundle\Doctrine\Converter\FilterQuery;

use Doctrine\DBAL\Query\Expression\CompositeExpression;
use ATSearchBundle\Doctrine\Converter\InputQueryToDoctrineConverterInterface;
use ATSearchBundle\Doctrine\Converter\InputQueryToDoctrineQueryFilters;
use ATSearchBundle\Doctrine\JoinAwareQueryBuilder;
use ATSearchBundle\Exception\NoConverterException;
use ATSearchBundle\Query\Filter\LogicalNot;
use ATSearchBundle\Query\FilterQueryCriterion;

readonly class LogicalNotConverter implements InputQueryToDoctrineConverterInterface
{
    /**
     * @throws NoConverterException
     */
    public function convert(
        FilterQueryCriterion $criterion,
        JoinAwareQueryBuilder $qb,
        InputQueryToDoctrineQueryFilters $converter
    ): CompositeExpression|string {
        if (!$criterion instanceof LogicalNot) {
            throw new \InvalidArgumentException('Unsupported criteria');
        }

        return sprintf(
            'NOT (%s)',
            $converter->convert($criterion->criterion, $qb)
        );
    }

    public function supports(FilterQueryCriterion $criteria): bool
    {
        return $criteria instanceof LogicalNot;
    }
}