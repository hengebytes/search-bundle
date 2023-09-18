<?php

namespace ATSearchBundle\Doctrine\Converter\FilterQuery;

use Doctrine\DBAL\Query\Expression\CompositeExpression;
use ATSearchBundle\Doctrine\Converter\InputQueryToDoctrineConverterInterface;
use ATSearchBundle\Doctrine\Converter\InputQueryToDoctrineQueryFilters;
use ATSearchBundle\Doctrine\JoinAwareQueryBuilder;
use ATSearchBundle\Exception\NoConverterException;
use ATSearchBundle\Query\Filter\LogicalOr;
use ATSearchBundle\Query\FilterQueryCriterion;

readonly class LogicalOrConverter implements InputQueryToDoctrineConverterInterface
{
    /**
     * @throws NoConverterException
     */
    public function convert(
        FilterQueryCriterion $criterion,
        JoinAwareQueryBuilder $qb,
        InputQueryToDoctrineQueryFilters $converter
    ): CompositeExpression|string {
        if (!$criterion instanceof LogicalOr) {
            throw new \InvalidArgumentException('Unsupported criteria');
        }
        if (!$criterion->criteria) {
            throw new \RuntimeException('Invalid criteria in LogicalOr criterion.');
        }

        $subexpressions = [];
        foreach ($criterion->criteria as $criterionItem) {
            $subexpressions[] = $converter->convert($criterionItem, $qb);
        }

        return $qb->queryBuilder->expr()->orX(...$subexpressions);
    }

    public function supports(FilterQueryCriterion $criteria): bool
    {
        return $criteria instanceof LogicalOr;
    }
}