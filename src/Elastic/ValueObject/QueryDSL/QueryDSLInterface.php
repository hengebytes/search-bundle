<?php

namespace ATernovtsii\SearchBundle\Elastic\ValueObject\QueryDSL;

interface QueryDSLInterface
{
    public function toArray(): array;
}