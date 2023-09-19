<?php

namespace ATSearchBundle\Search\ValueObject\QueryDSL;

interface QueryDSLInterface
{
    public function toArray(): array;
}