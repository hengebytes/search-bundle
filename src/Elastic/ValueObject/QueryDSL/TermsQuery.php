<?php

namespace ATSearchBundle\Elastic\ValueObject\QueryDSL;

final readonly class TermsQuery implements QueryDSLInterface
{
    public function __construct(private string $field, private array $value)
    {
    }

    public function toArray(): array
    {
        return [
            'terms' => [
                $this->field => $this->value,
            ],
        ];
    }
}
