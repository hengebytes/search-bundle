<?php

namespace ATernovtsii\SearchBundle\Elastic\Mapper;

interface IndexDocumentInterface
{
    public function getEntityClassName(): string;

    public function getIndexName(): string;

    public function getTenantId(object $entity): string;

    public function getFields(object $entity): array;

    public function getCustomFields(object $entity): array;

    public function getESFieldName(string $fieldName): ?string;

}