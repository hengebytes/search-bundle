<?php

namespace ATSearchBundle\Search\Converter;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use ATSearchBundle\Query\FilterQueryCriterion;

#[AutoconfigureTag('at_search.search.query.filter_converter')]
interface FilterInputQueryToElasticConverterInterface
{
    // This constant is used for fields that are not used for filtering.
    public const IGNORED_FIELD = 'ES_IGNORED';

    public function convert(FilterQueryCriterion $criterion, InputQueryToElasticFilter $converter): array;

    public function supports(FilterQueryCriterion $criteria): bool;
}