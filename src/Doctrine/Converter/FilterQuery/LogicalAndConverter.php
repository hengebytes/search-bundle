<?php

namespace ATernovtsii\SearchBundle\Doctrine\Converter\FilterQuery;

use Doctrine\DBAL\Query\Expression\CompositeExpression;
use ATernovtsii\SearchBundle\Doctrine\Converter\InputQueryToDoctrineConverterInterface;
use ATernovtsii\SearchBundle\Doctrine\Converter\InputQueryToDoctrineQueryFilters;
use ATernovtsii\SearchBundle\Doctrine\JoinAwareQueryBuilder;
use ATernovtsii\SearchBundle\Query\Filter\LogicalAnd;
use ATernovtsii\SearchBundle\Query\FilterQueryCriterion;

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