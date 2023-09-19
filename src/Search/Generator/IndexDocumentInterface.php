<?php

namespace ATSearchBundle\Search\Generator;

interface IndexDocumentInterface
{
    public function getEntityClassName(): string;

    public function getIndexName(): string;

    public function getTenantId(object $entity): string;

    public function getFields(object $entity): array;
}