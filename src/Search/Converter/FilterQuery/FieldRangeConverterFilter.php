<?php

namespace ATSearchBundle\Search\Converter\FilterQuery;

use ATSearchBundle\Search\Converter\FilterInputQueryToSearchQueryConverterInterface;
use ATSearchBundle\Search\Converter\InputQueryToSearchFilter;
use ATSearchBundle\Search\Resolver\FieldNameResolver;
use ATSearchBundle\Search\Resolver\FieldTypeResolver;
use ATSearchBundle\Search\ValueObject\QueryDSL\RangeQuery;
use ATSearchBundle\Query\Filter\CustomFieldFilter;
use ATSearchBundle\Query\Filter\FieldFilter;
use ATSearchBundle\Query\FilterQueryCriterion;
use ATSearchBundle\Query\Operator;
use InvalidArgumentException;
use function in_array;

readonly class FieldRangeConverterFilter implements FilterInputQueryToSearchQueryConverterInterface
{
    public function __construct(
        private FieldNameResolver $fieldNameResolver, private FieldTypeResolver $fieldTypeResolver
    ) {
    }

    public function convert(FilterQueryCriterion $criterion, InputQueryToSearchFilter $converter): array
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

        return (new RangeQuery($ESFieldName, $criterion->operator, $value))->toArray();
    }

    public function supports(FilterQueryCriterion $criteria): bool
    {
        return ($criteria instanceof FieldFilter || $criteria instanceof CustomFieldFilter)
            && in_array($criteria->operator, [Operator::GT, Operator::GTE, Operator::LT, Operator::LTE], true);
    }
}