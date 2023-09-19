<?php

namespace ATSearchBundle\Annotation;

use Attribute;

#[Attribute(Attribute::TARGET_FUNCTION | Attribute::TARGET_METHOD | Attribute::TARGET_PROPERTY)]
final readonly class TenantId
{
    public function __construct(public ?string $subFields = null)
    {
    }
}