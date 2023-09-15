<?php

namespace ATernovtsii\SearchBundle\Elastic\Generator;

use ATernovtsii\SearchBundle\Elastic\Converter\FilterInputQueryToElasticConverterInterface;
use ATernovtsii\SearchBundle\Elastic\Mapper\SchemaMapper;
use ATernovtsii\SearchBundle\Enum\FieldType;

readonly class DocumentFieldMetadata
{
    public function __construct(
        private string $fieldName,
        private ?FieldType $type,
        private string $valueResolver,
        private string $originalFieldName,
        private ?string $subFields
    ) {
    }

    public function getFieldNamesForMap(): array
    {
        $fieldName = $this->originalFieldName;
        if ($this->subFields) {
            $fieldName .= '.' . $this->subFields;
        }
        if (!$this->type) {
            return [$fieldName => FilterInputQueryToElasticConverterInterface::IGNORED_FIELD];
        }

        return [$fieldName => $this->fieldName . SchemaMapper::getSuffixByCustomType($this->type->value)];
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