<?php

namespace ATSearchBundle\ValueObject;

readonly class Result
{
    /** @var int|callable */
    public mixed $totalCount;
    /** @var callable|array */
    public mixed $data;

    public function __construct(
        callable|int|null $totalCount,
        callable|array $data,
    ) {
        $this->totalCount = $totalCount;
        $this->data = $data;
    }

    public function getTotalCount(): ?int
    {
        if (is_callable($this->totalCount)) {
            return ($this->totalCount)();
        }

        return $this->totalCount;
    }

    public function getData(): array
    {
        if (is_callable($this->data)) {
            return ($this->data)();
        }

        return $this->data;
    }
}