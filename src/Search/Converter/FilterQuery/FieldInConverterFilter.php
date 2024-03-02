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
use ATSearchBundle\Search\ValueObject\QueryDSL\{TermQuery, TermsQuery};
use InvalidArgumentException;
use function is_array;

readonly class FieldInConverterFilter implements FilterInputQueryToSearchQueryConverterInterface
{
    public function __construct(private FieldNameResolver $fieldNameResolver, private FieldTypeResolver $fieldTypeResolver)
    {
    }

    public function convert(FilterQueryCriterion $criterion, InputQueryToSearchFilter $converter): array
    {
        if (!$criterion instanceof FieldFilter
            && !$criterion instanceof CustomFieldFilter
            && !$criterion instanceof RelationFieldFilter
        ) {
            throw new InvalidArgumentException('Unsupported criteria');
        }

        if ($criterion instanceof RelationFieldFilter) {
            $ESFieldName = $this->fieldNameResolver->resolve(implode('.', $criterion->fields));
        } elseif ($criterion instanceof CustomFieldFilter) {
            $ESFieldName = $criterion->field;
        } else {
            $ESFieldName = $this->fieldNameResolver->resolve($criterion->field);
        }

        if ($ESFieldName === FilterInputQueryToSearchQueryConverterInterface::IGNORED_FIELD) {
            return [];
        }

        if (!$ESFieldName) {
            throw new CriterionFieldNotIndexedException($criterion);
        }

        $value = $this->fieldTypeResolver->resolveValueByFieldName($ESFieldName, $criterion->value);

        if (!is_array($value)) {
            $qb = new TermQuery($ESFieldName, $value);
        } else {
            $qb = new TermsQuery($ESFieldName, $value);
        }

        return $qb->toArray();
    }

    public function supports(FilterQueryCriterion $criteria): bool
    {
        return (
                $criteria instanceof FieldFilter
                || $criteria instanceof CustomFieldFilter
                || $criteria instanceof RelationFieldFilter
            )
            && in_array($criteria->operator, [Operator::IN, Operator::EQ], true);
    }
}