<?php

namespace ATSearchBundle\Elastic\ValueObject\QueryDSL;

readonly class RangeBetweenQuery implements QueryDSLInterface
{
    public function __construct(
        private string $field, private string|int $valueA, private string|int $valueB
    ) {
    }

    public function toArray(): array
    {
        return [
            'range' => [
                $this->field => [
                    'gte' => $this->valueA,
                    'lte' => $this->valueB,
                ],
            ],
        ];
    }
}