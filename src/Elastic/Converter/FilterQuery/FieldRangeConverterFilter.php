<?php

namespace ATernovtsii\SearchBundle\Elastic\Converter\FilterQuery;

use ATernovtsii\SearchBundle\Elastic\Converter\FilterInputQueryToElasticConverterInterface;
use ATernovtsii\SearchBundle\Elastic\Converter\InputQueryToElasticFilter;
use ATernovtsii\SearchBundle\Elastic\Resolver\FieldNameResolver;
use ATernovtsii\SearchBundle\Elastic\Resolver\FieldTypeResolver;
use ATernovtsii\SearchBundle\Elastic\ValueObject\QueryDSL\RangeQuery;
use ATernovtsii\SearchBundle\Query\Filter\CustomFieldFilter;
use ATernovtsii\SearchBundle\Query\Filter\FieldFilter;
use ATernovtsii\SearchBundle\Query\FilterQueryCriterion;
use ATernovtsii\SearchBundle\Query\Operator;
use InvalidArgumentException;
use function in_array;

readonly class FieldRangeConverterFilter implements FilterInputQueryToElasticConverterInterface
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

        return (new RangeQuery($ESFieldName, $criterion->operator, $value))->toArray();
    }

    public function supports(FilterQueryCriterion $criteria): bool
    {
        return ($criteria instanceof FieldFilter || $criteria instanceof CustomFieldFilter)
            && in_array($criteria->operator, [Operator::GT, Operator::GTE, Operator::LT, Operator::LTE], true);
    }
}