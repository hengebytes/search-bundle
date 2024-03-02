<?php

namespace ATSearchBundle\Search\Converter\FilterQuery;

use ATSearchBundle\Exception\CriterionFieldNotIndexedException;
use ATSearchBundle\Query\Filter\CustomFieldFilter;
use ATSearchBundle\Query\Filter\FieldFilter;
use ATSearchBundle\Query\Filter\RelationFieldFilter;
use ATSearchBundle\Query\FilterQueryCriterion;
use ATSearchBundle\Query\Operator;
use ATSearchBundle\Search\Converter\FilterInputQueryToSearchQueryConverterInterface;
use ATSearchBundle\Search\Converter\InputQueryToSearchFilter;
use ATSearchBundle\Search\Resolver\FieldNameResolver;
use ATSearchBundle\Search\Resolver\FieldTypeResolver;
use ATSearchBundle\Search\ValueObject\QueryDSL\WildcardQuery;
use InvalidArgumentException;

readonly class FieldLikeConverterFilter implements FilterInputQueryToSearchQueryConverterInterface
{
    public function __construct(
        private FieldNameResolver $fieldNameResolver, private FieldTypeResolver $fieldTypeResolver
    ) {
    }

    public function convert(FilterQueryCriterion $criterion, InputQueryToSearchFilter $converter): array
    {
        $isCustomField = $criterion instanceof CustomFieldFilter;
        if (!$criterion instanceof FieldFilter && !$isCustomField && !$criterion instanceof RelationFieldFilter) {
            throw new InvalidArgumentException('Unsupported criteria');
        }
        if ($criterion instanceof RelationFieldFilter) {
            $ESFieldName = $this->fieldNameResolver->resolve(implode('.', $criterion->fields));
        } elseif ($isCustomField) {
            $ESFieldName = $criterion->field;
        } else {
            $ESFieldName = $this->fieldNameResolver->resolve($criterion->field);
        }

        if (!$ESFieldName) {
            throw new CriterionFieldNotIndexedException($criterion);
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