<?php

namespace ATSearchBundle\Elastic\ValueObject\QueryDSL;

interface QueryDSLInterface
{
    public function toArray(): array;
}