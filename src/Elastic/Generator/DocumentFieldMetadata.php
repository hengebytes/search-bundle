<?php

namespace ATernovtsii\SearchBundle\Elastic\Generator;

use ATernovtsii\SearchBundle\Elastic\Converter\FilterInputQueryToElasticConverterInterface;
use ATernovtsii\SearchBundle\Elastic\Mapper\SchemaMapper;
use ATernovtsii\SearchBundle\Enum\FieldType;

readonly class DocumentFieldMetadata
{
    public function __construct(
        public string $fieldName,
        public ?FieldType $type,
        public string $valueResolver,
        public string $originalFieldName,
        private ?string $subField
    ) {
    }

    public function getFieldNamesForMap(): array
    {
        $fieldName = $this->fieldName;
        if ($this->subField) {
            $fieldName .= '.' . $this->subField;
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