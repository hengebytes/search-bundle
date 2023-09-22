<?php

namespace ATSearchBundle\Search\Converter;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use ATSearchBundle\Query\SortQueryCriterion;

#[AutoconfigureTag('at_search.search.query.sort_converter')]
interface SortInputQueryToSearchQueryConverterInterface
{
    public function convert(SortQueryCriterion $sortClause, InputQueryToSearchSort $converter): array;

    public function supports(SortQueryCriterion $sortClause): bool;
}