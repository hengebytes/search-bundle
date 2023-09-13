<?php

namespace ATernovtsii\SearchBundle\Elastic\Converter;

use ATernovtsii\SearchBundle\Elastic\Resolver\DocumentResolver;

readonly class FieldNameResolver
{
    public function __construct(private DocumentResolver $documentResolver)
    {
    }

    public function resolve(string $fieldName): ?string
    {
        return $this->documentResolver->getESFieldName($fieldName);
    }

    public function resolveCustom(string $fieldName): ?string
    {
        return $this->documentResolver->getCustomESFieldName($fieldName);
    }
}