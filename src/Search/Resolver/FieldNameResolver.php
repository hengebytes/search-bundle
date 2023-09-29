<?php

namespace ATSearchBundle\Search\Resolver;

use ATSearchBundle\Search\Provider\IndexDocumentProvider;

readonly class FieldNameResolver
{
    public function __construct(private IndexDocumentProvider $documentResolver)
    {
    }

    public function resolve(string $fieldName): ?string
    {
        return $this->documentResolver->getESFieldName($fieldName);
    }
}