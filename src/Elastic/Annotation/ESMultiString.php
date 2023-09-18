<?php

namespace ATSearchBundle\Elastic\Annotation;

use Attribute;

#[Attribute(Attribute::TARGET_FUNCTION | Attribute::TARGET_METHOD | Attribute::TARGET_PROPERTY)]
final readonly class ESMultiString extends ESField
{
}