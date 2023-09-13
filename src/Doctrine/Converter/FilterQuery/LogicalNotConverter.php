<?php

namespace ATernovtsii\SearchBundle\Doctrine\Converter\FilterQuery;

use Doctrine\DBAL\Query\Expression\CompositeExpression;
use ATernovtsii\SearchBundle\Doctrine\Converter\InputQueryToDoctrineConverterInterface;
use ATernovtsii\SearchBundle\Doctrine\Converter\InputQueryToDoctrineQueryFilters;
use ATernovtsii\SearchBundle\Doctrine\JoinAwareQueryBuilder;
use ATernovtsii\SearchBundle\Exception\NoConverterException;
use ATernovtsii\SearchBundle\Query\Filter\LogicalNot;
use ATernovtsii\SearchBundle\Query\FilterQueryCriterion;

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