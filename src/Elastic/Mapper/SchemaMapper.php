<?php

namespace ATernovtsii\SearchBundle\Elastic\Mapper;

use ATernovtsii\SearchBundle\Enum\FieldType;

abstract class SchemaMapper
{
    private static array $availableFieldTypes = [
        FieldType::INTEGER->value => 'integer',
        FieldType::MULTI_INTEGER->value => 'integer',
        FieldType::ID->value => 'keyword',
        FieldType::MULTI_ID->value => 'keyword',
        FieldType::STRING->value => 'keyword',
        FieldType::MULTI_STRING->value => 'keyword',
        FieldType::LONG->value => 'long',
        FieldType::MULTI_LONG->value => 'long',
        FieldType::TEXT->value => 'text',
        FieldType::BOOLEAN->value => 'boolean',
        FieldType::MULTI_BOOLEAN->value => 'boolean',
        FieldType::DATE->value => 'date',
        FieldType::FLOAT->value => 'float',
        FieldType::DOUBLE->value => 'double',
        FieldType::GEO_POINT->value => 'geo_point',
    ];

    private static array $fieldTypeSuffixes = [
        FieldType::INTEGER->value => '_i',
        FieldType::MULTI_INTEGER->value => '_mi',
        FieldType::ID->value => '_id',
        FieldType::MULTI_ID->value => '_mid',
        FieldType::STRING->value => '_s',
        FieldType::MULTI_STRING->value => '_ms',
        FieldType::LONG->value => '_l',
        FieldType::MULTI_LONG->value => '_ml',
        FieldType::TEXT->value => '_t',
        FieldType::BOOLEAN->value => '_b',
        FieldType::MULTI_BOOLEAN->value => '_mb',
        FieldType::DATE->value => '_dt',
        FieldType::FLOAT->value => '_f',
        FieldType::DOUBLE->value => '_d',
        FieldType::GEO_POINT->value => '_gl',
    ];

    public static function getCustomFieldTypeByESFieldName(string $fieldName): ?string
    {
        foreach (self::$fieldTypeSuffixes as $fieldType => $postfix) {
            if (str_ends_with($fieldName, $postfix)) {
                return $fieldType;
            }
        }

        return null;
    }

    public static function getESFieldTypeByCustomFieldType(?string $fieldName): ?string
    {
        if (!$fieldName) {
            return null;
        }

        return self::$availableFieldTypes[$fieldName] ?? null;
    }

    public static function getAvailableFieldTypes(): array
    {
        return self::$availableFieldTypes;
    }

    public static function getSuffixByCustomType($type): string
    {
        return self::$fieldTypeSuffixes[$type] ?? '';
    }
}