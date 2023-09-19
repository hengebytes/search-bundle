<?php

namespace ATSearchBundle\Annotation;

abstract readonly class BaseField
{
    public function __construct(
        public ?string $subFields = null, public ?string $storageName = null, public ?string $fieldName = null
    ) {
    }
}