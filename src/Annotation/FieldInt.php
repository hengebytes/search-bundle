<?php

namespace ATSearchBundle\Annotation;

use Attribute;

#[Attribute(Attribute::TARGET_FUNCTION | Attribute::TARGET_METHOD | Attribute::TARGET_PROPERTY)]
final readonly class FieldInt extends BaseField
{
}