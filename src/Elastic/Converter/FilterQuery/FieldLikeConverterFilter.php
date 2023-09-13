<?php

namespace ATernovtsii\SearchBundle\Elastic\Converter\FilterQuery;

use InvalidArgumentException;
use ATernovtsii\SearchBundle\Elastic\Converter\FieldNameResolver;
use ATernovtsii\SearchBundle\Elastic\Converter\FieldTypeResolver;
use ATernovtsii\SearchBundle\Elastic\Converter\FilterInputQueryToElasticConverterInterface;
use ATernovtsii\SearchBundle\Elastic\Converter\InputQueryToElasticFilter;
use ATernovtsii\SearchBundle\Elastic\ValueObject\QueryDSL\WildcardQuery;
use ATernovtsii\SearchBundle\Query\Filter\CustomFieldFilter;
use ATernovtsii\SearchBundle\Query\Filter\FieldFilter;
use ATernovtsii\SearchBundle\Query\Filter\RelationFieldFilter;
use ATernovtsii\SearchBundle\Query\FilterQueryCriterion;
use ATernovtsii\SearchBundle\Query\Operator;

readonly class FieldLikeConverterFilter implements FilterInputQueryToElasticConverterInterface
{
    public function __construct(
        private FieldNameResolver $fieldNameResolver, private FieldTypeResolver $fieldTypeResolver
    ) {
    }

    public function convert(FilterQueryCriterion $criterion, InputQueryToElasticFilter $converter): array
    {
        $isCustomField = $criterion instanceof CustomFieldFilter;
        if (!$criterion instanceof FieldFilter && !$isCustomField && !$criterion instanceof RelationFieldFilter) {
            throw new InvalidArgumentException('Unsupported criteria');
        }
        if ($criterion instanceof RelationFieldFilter) {
            $ESFieldName = $this->fieldNameResolver->resolve(implode('.', $criterion->fields));
        } elseif ($isCustomField) {
            $ESFieldName = $this->fieldNameResolver->resolveCustom($criterion->field);
        } else {
            $ESFieldName = $this->fieldNameResolver->resolve($criterion->field);
        }

        if (!$ESFieldName) {
            throw new InvalidArgumentException('Unsupported criteria. Field is not indexed.');
        }
        $value = $this->fieldTypeResolver->resolveValueByFieldName($ESFieldName, $criterion->value);

        return (new WildcardQuery($ESFieldName, $value))->toArray();
    }

    public function supports(FilterQueryCriterion $criteria): bool
    {
        return (
                $criteria instanceof FieldFilter
                || $criteria instanceof CustomFieldFilter
                || $criteria instanceof RelationFieldFilter
            )
            && $criteria->operator === Operator::LIKE;
    }
}