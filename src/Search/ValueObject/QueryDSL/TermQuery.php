<?php

namespace ATSearchBundle\Search\ValueObject\QueryDSL;

final readonly class TermQuery implements QueryDSLInterface
{
    public function __construct(private string $field, private mixed $value)
    {
    }

    public function toArray(): array
    {
        return [
            'term' => [
                $this->field => $this->value,
            ],
        ];
    }
}
