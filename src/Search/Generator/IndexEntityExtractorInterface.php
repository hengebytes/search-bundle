<?php

namespace ATSearchBundle\Search\Generator;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('at_search.elastic.index.extractor')]
interface IndexEntityExtractorInterface
{
    public function supports(object $entity): bool;

    public function extract(object $entity): array;

}