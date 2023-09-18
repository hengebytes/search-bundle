<?php

namespace ATSearchBundle\Doctrine\Converter\FilterQuery;

use Doctrine\DBAL\Query\Expression\CompositeExpression;
use ATSearchBundle\Doctrine\Converter\InputQueryToDoctrineConverterInterface;
use ATSearchBundle\Doctrine\Converter\InputQueryToDoctrineQueryFilters;
use ATSearchBundle\Doctrine\JoinAwareQueryBuilder;
use ATSearchBundle\Query\Filter\LogicalAnd;
use ATSearchBundle\Query\FilterQueryCriterion;

readonly class LogicalAndConverter implements InputQueryToDoctrineConverterInterface
{
    public function convert(
        FilterQueryCriterion $criterion,
        JoinAwareQueryBuilder $qb,
        InputQueryToDoctrineQueryFilters $converter
    ): CompositeExpression|string {
        if (!$criterion instanceof LogicalAnd) {
            throw new \InvalidArgumentException('Unsupported criteria');
        }
        if (!$criterion->criteria) {
            throw new \RuntimeException('Invalid criteria in LogicalAnd criterion.');
        }

        $subexpressions = [];
        foreach ($criterion->criteria as $criterionItem) {
            $subexpressions[] = $converter->convert($criterionItem, $qb);
        }

        return $qb->queryBuilder->expr()->andX(...$subexpressions);
    }

    public function supports(FilterQueryCriterion $criteria): bool
    {
        return $criteria instanceof LogicalAnd;
    }
}