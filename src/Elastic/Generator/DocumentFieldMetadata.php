<?php

namespace ATernovtsii\SearchBundle\Elastic\Generator;

use ATernovtsii\SearchBundle\Elastic\Converter\FilterInputQueryToElasticConverterInterface;
use ATernovtsii\SearchBundle\Elastic\FieldType;
use ATernovtsii\SearchBundle\Elastic\Mapper\SchemaMapper;

readonly class DocumentFieldMetadata
{
    public function __construct(
        public string $fieldName,
        public ?FieldType $type,
        public string $valueResolver,
        public string $originalFieldName,
    ) {
    }

    public function getFieldNamesForMap(): array
    {
        if (!$this->type) {
            return [$this->originalFieldName => FilterInputQueryToElasticConverterInterface::IGNORED_FIELD];
        }

        return [$this->originalFieldName => $this->fieldName . SchemaMapper::getSuffixByCustomType($this->type->value)];
    }

    public function getFieldNameWithResolver(): ?string
    {
        if (!$this->type) {
            return null;
        }

        return '\'' . $this->fieldName
            . SchemaMapper::getSuffixByCustomType($this->type->value)
            . '\' => ' . $this->valueResolver;
    }
}