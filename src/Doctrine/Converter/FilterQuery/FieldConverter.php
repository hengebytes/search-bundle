<?php

namespace ATSearchBundle\Doctrine\Converter\FilterQuery;

use Doctrine\DBAL\Query\Expression\CompositeExpression;
use InvalidArgumentException;
use ATSearchBundle\Doctrine\Converter\{InputQueryToDoctrineConverterInterface,
    InputQueryToDoctrineQueryFilters};
use ATSearchBundle\Doctrine\JoinAwareQueryBuilder;
use ATSearchBundle\Query\Filter\FieldFilter;
use ATSearchBundle\Query\FilterQueryCriterion;

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