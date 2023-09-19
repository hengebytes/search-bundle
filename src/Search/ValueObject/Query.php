<?php

namespace ATSearchBundle\Search\ValueObject;

class Query
{
    public string $indexName;
    public int $from = 0;
    public int $size = 500;
    public array $sort = [];
    public array $filters = [];
    public array $sourceIncludes = [];
    public bool $returnSource = true;
    public array $suggest = [];
    public int $tenantId;
    public bool $withCount = true;
}