<?php

namespace ATernovtsii\SearchBundle\Doctrine\Converter;

use Doctrine\DBAL\Query\Expression\CompositeExpression;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;
use ATernovtsii\SearchBundle\Doctrine\JoinAwareQueryBuilder;
use ATernovtsii\SearchBundle\Exception\NoConverterException;
use ATernovtsii\SearchBundle\Query\FilterQueryCriterion;

class InputQueryToDoctrineQueryFilters
{
    /** @var InputQueryToDoctrineConverterInterface[] */
    private iterable $converters;

    public function __construct(
        #[TaggedIterator('at_search.doctrine.query.filter')] iterable $converters,
    ) {
        $this->converters = $converters;
    }

    private function getConverter(FilterQueryCriterion $criteria): ?InputQueryToDoctrineConverterInterface
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
    public function convert(FilterQueryCriterion $criterion, JoinAwareQueryBuilder $queryBuilder): CompositeExpression|string
    {
        $converter = $this->getConverter($criterion);

        if ($converter === null) {
            throw new NoConverterException($criterion);
        }

        return $converter->convert($criterion, $queryBuilder, $this);
    }
}