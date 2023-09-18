<?php

namespace ATSearchBundle\Elastic\ValueObject;

class Document
{
    public static string $indexPrefix = 'at_search_';
    public string $tenantId;
    public array $body = [];
    public const DEFAULT_TENANT_ID = 't1';

    public function __construct(public string $id, public string $indexName)
    {
    }

    public function getIndex(): string
    {
        return self::$indexPrefix . $this->tenantId . '_' . $this->indexName;
    }
}