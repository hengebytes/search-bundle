<?php

namespace ATernovtsii\SearchBundle\Doctrine\Converter\FilterQuery;

use Doctrine\DBAL\Query\Expression\CompositeExpression;
use InvalidArgumentException;
use ATernovtsii\SearchBundle\Doctrine\Converter\InputQueryToDoctrineConverterInterface;
use ATernovtsii\SearchBundle\Doctrine\Converter\InputQueryToDoctrineQueryFilters;
use ATernovtsii\SearchBundle\Doctrine\JoinAwareQueryBuilder;
use ATernovtsii\SearchBundle\Query\Filter\RelationFieldFilter;
use ATernovtsii\SearchBundle\Query\FilterQueryCriterion;

class RelationFieldConverter extends BaseFieldConverter implements InputQueryToDoctrineConverterInterface
{
    public function convert(
        FilterQueryCriterion $criterion,
        JoinAwareQueryBuilder $qb,
        InputQueryToDoctrineQueryFilters $converter
    ): CompositeExpression|string {
        if (!$criterion instanceof RelationFieldFilter) {
            throw new InvalidArgumentException('Unsupported criteria');
        }

        $fields = $criterion->fields;
        $fieldName = array_pop($fields);
        $tableAlias = $fields[count($fields) - 1];

        $prevField = null;
        foreach ($fields as $field) {
            $qb->joins[$field] = !$prevField ? $qb->rootAlias : $prevField;
            $prevField = $field;
        }

        return $this->generateFieldExpression($criterion, $qb, $tableAlias, $fieldName);
    }

    public function supports(FilterQueryCriterion $criteria): bool
    {
        return $criteria instanceof RelationFieldFilter;
    }
}