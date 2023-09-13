<?php

namespace ATernovtsii\SearchBundle\Elastic\Converter;

use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;
use ATernovtsii\SearchBundle\Exception\NoConverterException;
use ATernovtsii\SearchBundle\Query\FilterQueryCriterion;

class InputQueryToElasticFilter
{
    /** @var FilterInputQueryToElasticConverterInterface[] */
    private iterable $converters;

    public function __construct(#[TaggedIterator('at_search.elastic.query.filter_converter')] iterable $converters)
    {
        $this->converters = $converters;
    }

    private function getConverter(FilterQueryCriterion $criteria): ?FilterInputQueryToElasticConverterInterface
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