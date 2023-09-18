<?php

namespace ATSearchBundle\Doctrine\Converter;

use Doctrine\DBAL\Query\Expression\CompositeExpression;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use ATSearchBundle\Doctrine\JoinAwareQueryBuilder;
use ATSearchBundle\Query\FilterQueryCriterion;

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