<?php

namespace ATSearchBundle\Search\ValueObject\QueryDSL;

use Doctrine\ORM\Query\Expr\Comparison;
use RuntimeException;

final readonly class RangeQuery implements QueryDSLInterface
{

    public function __construct(private string $field, private string $operator, private mixed $value)
    {
    }

    public function toArray(): array
    {
        return [
            'range' => [
                $this->field => [
                    $this->toElasticOpenSearchSearchOperator($this->operator) => $this->value,
                ],
            ],
        ];
    }

    private function toElasticOpenSearchSearchOperator(string $operator): string
    {
        return match ($operator) {
            Comparison::GT => 'gt',
            Comparison::GTE => 'gte',
            Comparison::LT => 'lt',
            Comparison::LTE => 'lte',
            default => throw new RuntimeException('Unsupported operator: ' . $operator),
        };
    }
}
