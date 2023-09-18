<?php

namespace ATSearchBundle\Elastic\Annotation;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final readonly class ESIndex
{
    public function __construct(public ?string $name = null)
    {
    }
}