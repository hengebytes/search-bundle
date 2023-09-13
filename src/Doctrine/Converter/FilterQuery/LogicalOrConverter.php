<?php

namespace ATernovtsii\SearchBundle\Doctrine\Converter\FilterQuery;

use Doctrine\DBAL\Query\Expression\CompositeExpression;
use ATernovtsii\SearchBundle\Doctrine\Converter\InputQueryToDoctrineConverterInterface;
use ATernovtsii\SearchBundle\Doctrine\Converter\InputQueryToDoctrineQueryFilters;
use ATernovtsii\SearchBundle\Doctrine\JoinAwareQueryBuilder;
use ATernovtsii\SearchBundle\Exception\NoConverterException;
use ATernovtsii\SearchBundle\Query\Filter\LogicalOr;
use ATernovtsii\SearchBundle\Query\FilterQueryCriterion;

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