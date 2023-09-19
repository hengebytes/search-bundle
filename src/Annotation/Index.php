<?php

namespace ATSearchBundle\Annotation;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final readonly class Index
{
    public function __construct(public ?string $name = null, public ?int $priority = null)
    {
    }
}