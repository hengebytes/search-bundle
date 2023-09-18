<?php

namespace ATSearchBundle\Elastic\Converter\FilterQuery;

use ATSearchBundle\Elastic\Converter\FilterInputQueryToElasticConverterInterface;
use ATSearchBundle\Elastic\Converter\InputQueryToElasticFilter;
use ATSearchBundle\Elastic\Resolver\FieldNameResolver;
use ATSearchBundle\Elastic\Resolver\FieldTypeResolver;
use ATSearchBundle\Elastic\ValueObject\QueryDSL\RangeBetweenQuery;
use ATSearchBundle\Query\Filter\CustomFieldFilter;
use ATSearchBundle\Query\Filter\FieldFilter;
use ATSearchBundle\Query\FilterQueryCriterion;
use ATSearchBundle\Query\Operator;
use InvalidArgumentException;
use function count;

readonly class FieldRangeBetweenConverterFilter implements FilterInputQueryToElasticConverterInterface
{
    public function __construct(
        private FieldNameResolver $fieldNameResolver, private FieldTypeResolver $fieldTypeResolver
    ) {
    }

    public function convert(FilterQueryCriterion $criterion, InputQueryToElasticFilter $converter): array
    {
        if (!$criterion instanceof FieldFilter && !$criterion instanceof CustomFieldFilter) {
            throw new InvalidArgumentException('Unsupported criteria');
        }
        if ($criterion instanceof CustomFieldFilter) {
            $ESFieldName = $criterion->field;
        } else {
            $ESFieldName = $this->fieldNameResolver->resolve($criterion->field);
        }

        if (!$ESFieldName) {
            throw new InvalidArgumentException('Unsupported criteria. Field is not indexed.');
        }
        $value = $this->fieldTypeResolver->resolveValueByFieldName($ESFieldName, $criterion->value);

        return (new RangeBetweenQuery($ESFieldName, $value[0], $value[1]))->toArray();
    }

    public function supports(FilterQueryCriterion $criteria): bool
    {
        return ($criteria instanceof FieldFilter || $criteria instanceof CustomFieldFilter)
            && $criteria->operator === Operator::BETWEEN
            && count($criteria->value) === 2;
    }
}