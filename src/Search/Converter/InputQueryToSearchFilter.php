<?php

namespace ATSearchBundle\Search\Converter;

use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;
use ATSearchBundle\Exception\NoConverterException;
use ATSearchBundle\Query\FilterQueryCriterion;

class InputQueryToSearchFilter
{
    /** @var FilterInputQueryToSearchQueryConverterInterface[] */
    private iterable $converters;

    public function __construct(#[TaggedIterator('at_search.search.query.filter_converter')] iterable $converters)
    {
        $this->converters = $converters;
    }

    private function getConverter(FilterQueryCriterion $criteria): ?FilterInputQueryToSearchQueryConverterInterface
    {
        foreach ($this->converters as $converter) {
            if ($converter->supports($criteria)) {
                return $converter;
            }
        }

        return null;
    }

    /**
     * @throws NoConverterException
     */
    public function convert(FilterQueryCriterion $criterion): array
    {
        $converter = $this->getConverter($criterion);

        if ($converter === null) {
            throw new NoConverterException($criterion);
        }

        return $converter->convert($criterion, $this);
    }
}