<?php

namespace ATSearchBundle\Search\Resolver;

use ATSearchBundle\Search\Mapper\SchemaMapper;

readonly class FieldTypeResolver
{
    public function resolveValueByFieldName(string $fieldName, $value): mixed
    {
        $customFieldType = SchemaMapper::getCustomFieldTypeByESFieldName($fieldName);

        $fieldType = SchemaMapper::getESFieldTypeByCustomFieldType($customFieldType);

        switch ($fieldType) {
            case 'keyword':
                if (is_array($value)) {
                    return array_map(static fn($v) => (string)$v, $value);
                }

                return (string)$value;
            case 'integer':
            case 'long':
                if (is_array($value)) {
                    return array_map(static fn($v) => (int)$v, $value);
                }

                return (int)$value;
            case 'geo_point':
            case 'text':
                if (is_array($value)) {
                    return array_map(static fn($v) => (string)$v, $value);
                }

                return (string)$value;
            case 'boolean':
                if (is_array($value)) {
                    return array_map(static fn($v) => (bool)$v, $value);
                }

                return (bool)$value;
            case 'date':
                if (is_array($value)) {
                    return array_map(static fn($v) => (string)$v, $value);
                }

                return (string)$value;
            case 'float':
            case 'double':
                if (is_array($value)) {
                    return array_map(static fn($v) => (float)$v, $value);
                }

                return (float)$value;
            default:
                return $value;
        }
    }

}