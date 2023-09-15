<?php

namespace ATernovtsii\SearchBundle\Elastic\Annotation;

use Attribute;

#[Attribute(Attribute::TARGET_FUNCTION | Attribute::TARGET_METHOD | Attribute::TARGET_PROPERTY)]
final readonly class ESTenantId extends ESField
{
    public function __construct(public ?string $subFields = null)
    {
        parent::__construct($subFields);
    }
}