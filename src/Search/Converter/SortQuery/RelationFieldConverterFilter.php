<?php

namespace ATSearchBundle\Search\Converter\SortQuery;

use ATSearchBundle\Exception\CriterionFieldNotIndexedException;
use ATSearchBundle\Query\SortClause\SortByRelationField;
use ATSearchBundle\Query\SortQueryCriterion;
use ATSearchBundle\Search\Converter\{InputQueryToSearchSort, SortInputQueryToSearchQueryConverterInterface};
use ATSearchBundle\Search\Resolver\FieldNameResolver;
use InvalidArgumentException;
use RuntimeException;

readonly class RelationFieldConverterFilter implements SortInputQueryToSearchQueryConverterInterface
{
    public function __construct(private FieldNameResolver $fieldNameResolver)
    {
    }

    public function convert(SortQueryCriterion $sortClause, InputQueryToSearchSort $converter): array
    {
        if (!$sortClause instanceof SortByRelationField) {
            throw new InvalidArgumentException('Unsupported sort criteria');
        }

        $field = $this->fieldNameResolver->resolve($sortClause->relationField . '.' . $sortClause->field);
        if (!$field) {
            throw new CriterionFieldNotIndexedException($sortClause);
        }

        return [$field => ['order' => $this->getDirection($sortClause)]];
    }

    public function supports(SortQueryCriterion $sortClause): bool
    {
        return $sortClause instanceof SortByRelationField;
    }

    protected function getDirection(SortByRelationField $sortClause): string
    {
        return match ($sortClause->direction) {
            'ASC' => 'asc',
            'DESC' => 'desc',
            default => throw new RuntimeException(
                "Invalid sort direction: {$sortClause->relationField}.{$sortClause->field} {$sortClause->direction}"
            ),
        };
    }
}