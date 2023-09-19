<?php

namespace ATSearchBundle\Search\Converter\SortQuery;

use ATSearchBundle\Search\Converter\{InputQueryToElasticSort, SortInputQueryToElasticConverterInterface};
use ATSearchBundle\Search\Resolver\FieldNameResolver;
use ATSearchBundle\Query\SortClause\SortByField;
use ATSearchBundle\Query\SortQueryCriterion;
use InvalidArgumentException;
use RuntimeException;

readonly class FieldConverterFilter implements SortInputQueryToElasticConverterInterface
{
    public function __construct(private FieldNameResolver $fieldNameResolver)
    {
    }

    public function convert(SortQueryCriterion $sortClause, InputQueryToElasticSort $converter): array
    {
        if (!$sortClause instanceof SortByField) {
            throw new InvalidArgumentException('Unsupported criteria');
        }

        return [
            $this->fieldNameResolver->resolve($sortClause->field) => ['order' => $this->getDirection($sortClause)],
        ];
    }

    public function supports(SortQueryCriterion $sortClause): bool
    {
        return $sortClause instanceof SortByField;
    }

    protected function getDirection(SortByField $sortClause): string
    {
        return match ($sortClause->direction) {
            'ASC' => 'asc',
            'DESC' => 'desc',
            default => throw new RuntimeException('Invalid sort direction: ' . $sortClause->direction),
        };
    }
}