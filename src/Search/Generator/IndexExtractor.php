<?php

namespace ATSearchBundle\Search\Generator;

use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;

class IndexExtractor
{
    /**
     * @var iterable<IndexEntityExtractorInterface>
     */
    private iterable $extractors;

    public function __construct(#[TaggedIterator('at_search.elastic.index.extractor')] iterable $extractors)
    {
        $this->extractors = $extractors;
    }

    public function extract(object $entity): array
    {
        $fields = [[]];
        foreach ($this->extractors as $extractor) {
            if ($extractor->supports($entity)) {
                $fields[] = $extractor->extract($entity);
            }
        }

        return array_merge(...$fields);
    }
}