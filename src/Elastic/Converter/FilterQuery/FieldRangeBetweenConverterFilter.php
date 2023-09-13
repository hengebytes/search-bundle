<?php

namespace ATernovtsii\SearchBundle\Elastic\Converter\FilterQuery;

use InvalidArgumentException;
use ATernovtsii\SearchBundle\Elastic\Converter\FieldNameResolver;
use ATernovtsii\SearchBundle\Elastic\Converter\FieldTypeResolver;
use ATernovtsii\SearchBundle\Elastic\Converter\FilterInputQueryToElasticConverterInterface;
use ATernovtsii\SearchBundle\Elastic\Converter\InputQueryToElasticFilter;
use ATernovtsii\SearchBundle\Elastic\ValueObject\QueryDSL\RangeBetweenQuery;
use ATernovtsii\SearchBundle\Query\Filter\CustomFieldFilter;
use ATernovtsii\SearchBundle\Query\Filter\FieldFilter;
use ATernovtsii\SearchBundle\Query\FilterQueryCriterion;
use ATernovtsii\SearchBundle\Query\Operator;
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
            $ESFieldName = $this->fieldNameResolver->resolveCustom($criterion->field);
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