<?php

namespace ATernovtsii\SearchBundle\Doctrine\Converter\FilterQuery;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Query\Expr\{Comparison, Func, Orx};
use ATernovtsii\SearchBundle\Doctrine\JoinAwareQueryBuilder;
use ATernovtsii\SearchBundle\Query\Filter\RelationFieldFilter;
use ATernovtsii\SearchBundle\Query\Filter\FieldFilter;
use ATernovtsii\SearchBundle\Query\Operator;
use function count;
use function is_array;

abstract class BaseFieldConverter
{
    protected function generateFieldExpression(
        RelationFieldFilter|FieldFilter $criterion,
        JoinAwareQueryBuilder $qb,
        mixed $tableAlias,
        mixed $fieldName
    ): Comparison|Func|string|Orx {
        $fieldValue = $criterion->value;

        if ($fieldValue === '' || $fieldValue === null) {
            return $qb->queryBuilder->expr()->isNull($tableAlias . '.' . $fieldName);
        }

        $fieldVariableName = $qb->getNewParamKey();
        $fieldVariable = ':' . $fieldVariableName;
        if ($criterion->operator === Operator::BETWEEN && is_array($fieldValue) && count($fieldValue) === 2) {
            $qb->queryBuilder->setParameter($fieldVariableName . '_b1', $fieldValue[0]);
            $qb->queryBuilder->setParameter($fieldVariableName . '_b2', $fieldValue[1]);
        } elseif ($criterion->operator === Operator::LIKE) {
            $qb->queryBuilder->setParameter($fieldVariableName, '%' . $fieldValue . '%');
        } else {
            $qb->queryBuilder->setParameter($fieldVariableName, $fieldValue);
        }

        if (is_array($fieldValue) && $criterion->operator !== Operator::BETWEEN) {
            $containsNull = false;
            foreach ($fieldValue as $key => $val) {
                if ($val === '' || $val === null) {
                    $containsNull = true;
                    unset($fieldValue[$key]);
                }
            }

            $inExpression = $qb->queryBuilder->expr()->in($tableAlias . '.' . $fieldName, $fieldVariable);

            if (!$containsNull) {
                return $inExpression;
            }
            $expressions = [
                $inExpression,
                $qb->queryBuilder->expr()->isNull($tableAlias . '.' . $fieldName),
            ];

            return $qb->queryBuilder->expr()->orX(...$expressions);
        }

        if ($criterion->type === Types::JSON) {
            return $qb->queryBuilder->expr()
                ->eq('JSON_CONTAINS (' . $tableAlias . '.' . $fieldName . ', ' . $fieldVariable . ')', 1);
        }

        return match ($criterion->operator) {
            Operator::LT => $qb->queryBuilder->expr()->lt($tableAlias . '.' . $fieldName, $fieldVariable),
            Operator::LTE => $qb->queryBuilder->expr()->lte($tableAlias . '.' . $fieldName, $fieldVariable),
            Operator::GT => $qb->queryBuilder->expr()->gt($tableAlias . '.' . $fieldName, $fieldVariable),
            Operator::GTE => $qb->queryBuilder->expr()->gte($tableAlias . '.' . $fieldName, $fieldVariable),
            Operator::EQ => $qb->queryBuilder->expr()->eq($tableAlias . '.' . $fieldName, $fieldVariable),
            Operator::LIKE => $qb->queryBuilder->expr()->like($tableAlias . '.' . $fieldName, $fieldVariable),
            Operator::BETWEEN => $qb->queryBuilder->expr()->between(
                $tableAlias . '.' . $fieldName, $fieldVariable . '_b1', $fieldVariable . '_b2'
            ),
        };

    }
}