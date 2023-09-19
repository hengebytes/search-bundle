<?php

namespace ATSearchBundle\Search\Generator;

use ATSearchBundle\Search\Converter\FilterInputQueryToElasticConverterInterface;
use ATSearchBundle\Search\Mapper\SchemaMapper;
use ATSearchBundle\Enum\FieldType;

readonly class DocumentFieldMetadata
{
    public function __construct(
        private string $storageFieldName,
        private ?FieldType $type,
        private string $valueResolver,
        private string $originalFieldName,
        private ?string $subFields,
        private ?string $fieldName
    ) {
    }

    public function getFieldNamesForMap(): array
    {
        $fieldName = $this->fieldName ?? $this->originalFieldName;
        if ($this->subFields && !$this->fieldName) {
            $fieldName .= '.' . $this->subFields;
        }
        if (!$this->type) {
            return [$fieldName => FilterInputQueryToElasticConverterInterface::IGNORED_FIELD];
        }

        return [$fieldName => $this->storageFieldName . SchemaMapper::getSuffixByCustomType($this->type->value)];
    }

    public function getFieldNameWithResolver(): ?string
    {
        if (!$this->type) {
            return null;
        }

        return '\'' . $this->storageFieldName
            . SchemaMapper::getSuffixByCustomType($this->type->value)
            . '\' => ' . $this->valueResolver;
    }

    public function isMulti(): bool
    {
        return in_array($this->type, [FieldType::MULTI_STRING, FieldType::MULTI_INTEGER, FieldType::MULTI_BOOLEAN], true);
    }
}