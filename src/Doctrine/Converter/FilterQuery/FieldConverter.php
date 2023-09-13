<?php

namespace ATernovtsii\SearchBundle\Doctrine\Converter\FilterQuery;

use Doctrine\DBAL\Query\Expression\CompositeExpression;
use InvalidArgumentException;
use ATernovtsii\SearchBundle\Doctrine\Converter\{InputQueryToDoctrineConverterInterface,
    InputQueryToDoctrineQueryFilters};
use ATernovtsii\SearchBundle\Doctrine\JoinAwareQueryBuilder;
use ATernovtsii\SearchBundle\Query\Filter\FieldFilter;
use ATernovtsii\SearchBundle\Query\FilterQueryCriterion;

class FieldConverter extends BaseFieldConverter implements InputQueryToDoctrineConverterInterface
{
    public function convert(
        FilterQueryCriterion $criterion,
        JoinAwareQueryBuilder $qb,
        InputQueryToDoctrineQueryFilters $converter
    ): CompositeExpression|string {
        if (!$criterion instanceof FieldFilter) {
            throw new InvalidArgumentException('Unsupported criteria');
        }
        $fieldName = $criterion->field;

        $alias = $qb->rootAlias;
        $tableAlias = $alias;

        return $this->generateFieldExpression($criterion, $qb, $tableAlias, $fieldName);
    }

    public function supports(FilterQueryCriterion $criteria): bool
    {
        return $criteria instanceof FieldFilter;
    }
}