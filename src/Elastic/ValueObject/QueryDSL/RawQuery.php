<?php

namespace ATSearchBundle\Elastic\ValueObject\QueryDSL;

final readonly class RawQuery implements QueryDSLInterface
{
    public function __construct(private array $query)
    {
    }

    public function toArray(): array
    {
        return $this->query;
    }
}
