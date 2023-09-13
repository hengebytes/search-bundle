<?php

namespace ATernovtsii\SearchBundle\Elastic\ValueObject\QueryDSL;

class BoolQuery implements QueryDSLInterface
{
    /** @var QueryDSLInterface[] */
    private array $must = [];

    /** @var QueryDSLInterface[] */
    private array $filter = [];

    /** @var QueryDSLInterface[] */
    private array $should = [];

    /** @var QueryDSLInterface[] */
    private array $mustNot = [];

    private int $minimumShouldMatch = 1;


    public function addMust(QueryDSLInterface $node): self
    {
        $this->must[] = $node;

        return $this;
    }

    public function addFilter(QueryDSLInterface $node): self
    {
        $this->filter[] = $node;

        return $this;
    }

    public function addShould(QueryDSLInterface $node): self
    {
        $this->should[] = $node;

        return $this;
    }

    public function addMustNot(QueryDSLInterface $node): self
    {
        $this->mustNot[] = $node;

        return $this;
    }

    public function setMinimumShouldMatch(int $minimumShouldMatch): self
    {
        $this->minimumShouldMatch = $minimumShouldMatch;

        return $this;
    }

    public function toArray(): array
    {
        $query = [];

        if ($this->must) {
            $query['must'] = $this->build($this->must);
        }

        if ($this->filter) {
            $query['filter'] = $this->build($this->filter);
        }

        if ($this->should) {
            $query['should'] = $this->build($this->should);
            $query['minimum_should_match'] = $this->minimumShouldMatch;
        }

        if ($this->mustNot) {
            $query['must_not'] = $this->build($this->mustNot);
        }

        return [
            'bool' => $query,
        ];
    }

    private function build(array $nodes): array
    {
        return array_values(array_filter(array_map(static function (QueryDSLInterface $node): array {
            return $node->toArray();
        }, $nodes)));
    }
}
