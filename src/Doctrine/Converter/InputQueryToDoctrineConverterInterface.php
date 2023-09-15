<?php

namespace ATernovtsii\SearchBundle\Doctrine\Converter;

use Doctrine\DBAL\Query\Expression\CompositeExpression;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use ATernovtsii\SearchBundle\Doctrine\JoinAwareQueryBuilder;
use ATernovtsii\SearchBundle\Query\FilterQueryCriterion;

#[AutoconfigureTag('at_search.doctrine.query.filter')]
interface InputQueryToDoctrineConverterInterface
{
    public function convert(
        FilterQueryCriterion $criterion,
        JoinAwareQueryBuilder $qb,
        InputQueryToDoctrineQueryFilters $converter
    ): CompositeExpression|string;

    public function supports(FilterQueryCriterion $criteria): bool;
}