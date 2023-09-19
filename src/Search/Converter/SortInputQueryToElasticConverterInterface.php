<?php

namespace ATSearchBundle\Search\Converter;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use ATSearchBundle\Query\SortQueryCriterion;

#[AutoconfigureTag('at_search.elastic.query.sort_converter')]
interface SortInputQueryToElasticConverterInterface
{
    public function convert(SortQueryCriterion $sortClause, InputQueryToElasticSort $converter): array;

    public function supports(SortQueryCriterion $sortClause): bool;
}