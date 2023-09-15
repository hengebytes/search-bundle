<?php

namespace ATernovtsii\SearchBundle\Elastic\Annotation;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final readonly class ESIndex extends ESField
{
}