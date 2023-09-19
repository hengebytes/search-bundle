<?php

namespace ATSearchBundle\Search\ValueObject\QueryDSL;

final readonly class QueryStringQuery implements QueryDSLInterface
{
    public function __construct(private string $field, private string $value)
    {
    }

    public function toArray(): array
    {
        $keywordWords = array_filter(explode(' ', $this->value));
        $q = '(*' . implode('* AND *', $keywordWords) . '*)';

        return [
            'query_string' => [
                'query' => $q,
                'fields' => [$this->field],
            ],
        ];
    }
}
