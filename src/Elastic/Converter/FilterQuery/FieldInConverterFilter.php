<?php

namespace ATernovtsii\SearchBundle\Elastic\Converter\FilterQuery;

use ATernovtsii\SearchBundle\Elastic\Converter\FilterInputQueryToElasticConverterInterface;
use ATernovtsii\SearchBundle\Elastic\Converter\InputQueryToElasticFilter;
use ATernovtsii\SearchBundle\Elastic\Resolver\FieldNameResolver;
use ATernovtsii\SearchBundle\Elastic\Resolver\FieldTypeResolver;
use ATernovtsii\SearchBundle\Elastic\ValueObject\QueryDSL\{TermQuery, TermsQuery};
use ATernovtsii\SearchBundle\Query\Filter\CustomFieldFilter;
use ATernovtsii\SearchBundle\Query\Filter\FieldFilter;
use ATernovtsii\SearchBundle\Query\Filter\RelationFieldFilter;
use ATernovtsii\SearchBundle\Query\FilterQueryCriterion;
use ATernovtsii\SearchBundle\Query\Operator;
use InvalidArgumentException;
use function is_array;

readonly class FieldInConverterFilter implements FilterInputQueryToElasticConverterInterface
{
    public function __construct(private FieldNameResolver $fieldNameResolver, private FieldTypeResolver $fieldTypeResolver)
    {
    }

    public function convert(FilterQueryCriterion $criterion, InputQueryToElasticFilter $converter): array
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
            $ESFieldName = $this->fieldNameResolver->resolveCustom($criterion->field);
        } else {
            $ESFieldName = $this->fieldNameResolver->resolve($criterion->field);
        }

        if ($ESFieldName === FilterInputQueryToElasticConverterInterface::IGNORED_FIELD) {
            return [];
        }

        if (!$ESFieldName) {
            throw new InvalidArgumentException('Unsupported criteria. Field is not indexed.');
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
        return ($criteria instanceof FieldFilter
                || $criteria instanceof CustomFieldFilter
                || $criteria instanceof RelationFieldFilter
            )
            && in_array($criteria->operator, [Operator::IN, Operator::EQ], true);
    }
}