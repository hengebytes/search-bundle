<?php

namespace ATSearchBundle\Elastic\ValueObject\QueryDSL;

final readonly class WildcardQuery implements QueryDSLInterface
{
    public function __construct(private string $field, private string $value)
    {
    }

    public function toArray(): array
    {
        return [
            'wildcard' => [
                $this->field => [
                    'value' => '*' . $this->value . '*',
                ],
            ],
        ];
    }
}
