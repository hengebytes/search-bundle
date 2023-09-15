<?php

namespace ATernovtsii\SearchBundle\Elastic\Generator;

use ATernovtsii\SearchBundle\Elastic\Annotation\ESBool;
use ATernovtsii\SearchBundle\Elastic\Annotation\ESCustomFieldMap;
use ATernovtsii\SearchBundle\Elastic\Annotation\ESDateTime;
use ATernovtsii\SearchBundle\Elastic\Annotation\ESId;
use ATernovtsii\SearchBundle\Elastic\Annotation\ESIgnored;
use ATernovtsii\SearchBundle\Elastic\Annotation\ESIndex;
use ATernovtsii\SearchBundle\Elastic\Annotation\ESInt;
use ATernovtsii\SearchBundle\Elastic\Annotation\ESMultiBool;
use ATernovtsii\SearchBundle\Elastic\Annotation\ESMultiInt;
use ATernovtsii\SearchBundle\Elastic\Annotation\ESMultiString;
use ATernovtsii\SearchBundle\Elastic\Annotation\ESString;
use ATernovtsii\SearchBundle\Elastic\ValueObject\Document;
use ATernovtsii\SearchBundle\Enum\FieldType;
use Murtukov\PHPCodeGenerator\Modifier;
use Murtukov\PHPCodeGenerator\PhpFile;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;

class IndexDocumentBuilder
{
    private const DOCBLOCK_TEXT = 'THIS FILE WAS GENERATED AND SHOULD NOT BE EDITED MANUALLY.';

    public function build(string $namespace, string $className): ?PhpFile
    {
        $indexName = '';
        $reflectionClass = new ReflectionClass($namespace . '\\' . $className);
        $attributes = $reflectionClass->getAttributes();
        $hasIndexAttribute = false;
        foreach ($attributes as $attribute) {
            $hasIndexAttribute = $attribute->getName() === ESIndex::class;
            if ($hasIndexAttribute) {
                $indexName = $attribute->getArguments()['name']
                    ?? $this->toSnakeCase($className);
                break;
            }
        }

        if (!$hasIndexAttribute) {
            return null;
        }
        $fields = [];
        $fieldMap = [[]];
        $customFieldsGetter = null;
        $documentClassName = $className . 'IndexDocument';

        $tenantIdFunc = Document::DEFAULT_TENANT_ID;
        $properties = $reflectionClass->getProperties();
        foreach ($properties as $property) {
            $attributes = $property->getAttributes();
            foreach ($attributes as $attribute) {
                $resolvedTenantIdFunc = $this->getTenantIdFunc($attribute, $property);
                if ($resolvedTenantIdFunc) {
                    $tenantIdFunc = $resolvedTenantIdFunc;
                }
                $fieldMetadata = $this->getFieldMetadata($attribute, $property);
                if ($fieldMetadata) {
                    $field = $fieldMetadata->getFieldNameWithResolver();
                    if ($field) {
                        $fields[] = $field;
                    }
                    $fieldMap[] = $fieldMetadata->getFieldNamesForMap();
                }
            }
        }

        $methods = $reflectionClass->getMethods();
        foreach ($methods as $method) {
            $attributes = $method->getAttributes();
            foreach ($attributes as $attribute) {
                $resolvedTenantIdFunc = $this->getTenantIdFunc($attribute, $method);
                if ($resolvedTenantIdFunc) {
                    $tenantIdFunc = $resolvedTenantIdFunc;
                }
                $fieldMetadata = $this->getFieldMetadata($attribute, $method);
                if ($fieldMetadata) {
                    $field = $fieldMetadata->getFieldNameWithResolver();
                    if ($field) {
                        $fields[] = $field;
                    }
                    $fieldMap[] = $fieldMetadata->getFieldNamesForMap();
                }
            }
        }

        $fileBuilder = PhpFile::new()->setNamespace('ATernovtsii\\SearchBundle\\DocumentMetadata');
        $class = $fileBuilder->createClass($documentClassName)->setFinal()
            ->addImplements(IndexDocumentInterface::class)
            ->addUse($namespace . '\\' . $className)
            ->setDocBlock(static::DOCBLOCK_TEXT)
            ->addProperty('fieldsMap', Modifier::PRIVATE, 'array', array_merge(...$fieldMap));

        $class->createMethod('getEntityClassName')
            ->setReturnType('string')
            ->setDocBlock('{@inheritdoc}')
            ->append("return $className::class");

        $class->createMethod('getIndexName')
            ->setReturnType('string')
            ->setDocBlock('{@inheritdoc}')
            ->append("return '$indexName'");

        $class->createMethod('getTenantId')
            ->setReturnType('string')
            ->setDocBlock('{@inheritdoc}')
            ->addArgument('entity', 'object')
            ->append("return $tenantIdFunc");

        $class->createMethod('getFields')
            ->setReturnType('array')
            ->setDocBlock('{@inheritdoc}')
            ->addArgument('entity', 'object')
            ->append("return [\n" . implode(",\n", $fields) . "\n]");

        $class->createMethod('getESFieldName')
            ->setReturnType('?string')
            ->setDocBlock('{@inheritdoc}')
            ->addArgument('fieldName', 'string')
            ->append('return $this->fieldsMap[$fieldName] ?? null');

        $class->createMethod('getCustomFields')
            ->setReturnType('array')
            ->setDocBlock('{@inheritdoc}')
            ->addArgument('entity', 'object')
            ->append("return " . ($customFieldsGetter ?: '[]'));

        return $fileBuilder;
    }

    private function getTenantIdFunc(ReflectionAttribute $attribute, ReflectionProperty|ReflectionMethod $property): ?string
    {
        if ($attribute->getName() !== 'ATernovtsii\SearchBundle\Elastic\Annotation\ESTenantId') {
            return null;
        }

        return $this->getEntityPropertyValue($attribute, $property);
    }

    private function getFieldMetadata(ReflectionAttribute $attribute, ReflectionProperty|ReflectionMethod $property): ?DocumentFieldMetadata
    {
        $type = match ($attribute->getName()) {
            ESId::class => FieldType::ID,
            ESString::class => FieldType::STRING,
            ESDateTime::class => FieldType::DATE,
            ESMultiString::class => FieldType::MULTI_STRING,
            ESMultiInt::class => FieldType::MULTI_INTEGER,
            ESInt::class => FieldType::INTEGER,
            ESBool::class => FieldType::BOOLEAN,
            ESMultiBool::class => FieldType::MULTI_BOOLEAN,
            ESIgnored::class => 'ignored',
            default => null,
        };

        if ($type === null) {
            return null;
        }

        return new DocumentFieldMetadata(
            $this->toSnakeCase($property->getName()),
            $type === 'ignored' ? null : $type,
            $this->getEntityPropertyValue($attribute, $property),
            $property->getName(),
            $attribute->getArguments()['subFields'] ?? null
        );
    }

    private function toSnakeCase(string $string): string
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $string));
    }

    private function getEntityPropertyValue(ReflectionAttribute $attribute, ReflectionProperty|ReflectionMethod $property): string
    {
        $nameOrFunc = $property->getName();
        if ($property instanceof ReflectionProperty && !$property->isPublic()) {
            $nameOrFunc = 'get' . ucfirst($nameOrFunc) . '()';
        }
        if ($property instanceof ReflectionMethod) {
            $nameOrFunc = $property->getName() . '()';
        }
        if (
            ($property instanceof ReflectionMethod && $property->getReturnType() == 'DateTimeInterface')
            || (
                $property instanceof ReflectionProperty
                && $property->getType()
                && $property->getType()->getName() === 'DateTimeInterface'
            )
        ) {
            $nameOrFunc .= '->format(\'c\')';
        }
        $getter = '$entity->' . $nameOrFunc;
        $subFieldFunc = '';
        $subField = $attribute->getArguments()['subFields'] ?? null;
        if ($subField) {
            if (str_contains($subField, '.')) {
                $subFields = explode('.', $subField);
                foreach ($subFields as $subField) {
                    $subFieldFunc .= '->' . $subField;
                }
            } else {
                $subFieldFunc .= '->' . $subField;
            }
        }

        if (in_array($attribute->getName(), [ESMultiString::class, ESMultiString::class], true)) {
            $getter = 'array_map(static fn($item) => $item' . $subFieldFunc . ', ' . $getter . ')';
        } else {
            $getter .= $subFieldFunc;
        }

        return $getter;
    }
}