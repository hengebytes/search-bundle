<?php

namespace ATSearchBundle\Search\Converter;

use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;
use ATSearchBundle\Exception\NoConverterException;
use ATSearchBundle\Query\SortQueryCriterion;

class InputQueryToElasticSort
{
    /** @var SortInputQueryToElasticConverterInterface[] */
    private iterable $converters;

    public function __construct(#[TaggedIterator('at_search.search.query.sort_converter')] iterable $converters)
    {
        $this->converters = $converters;
    }

    private function getConverter(SortQueryCriterion $sortClause): ?SortInputQueryToElasticConverterInterface
    {
        foreach ($this->converters as $converter) {
            if ($converter->supports($sortClause)) {
                return $converter;
            }
        }

        return null;
    }

    /**
     * @throws NoConverterException
     */
    public function convert(array $sortClauses): array
    {
        $converted = [];

        foreach ($sortClauses as $sortClause) {
            $converter = $this->getConverter($sortClause);
            if ($converter === null) {
                throw new NoConverterException($sortClause);
            }
            $converted[] = $converter->convert($sortClause, $this);
        }

        return $converted;
    }
}