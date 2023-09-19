<?php

namespace ATSearchBundle\Search\Generator;

use ATSearchBundle\Annotation\FieldBool;
use ATSearchBundle\Annotation\FieldDateTime;
use ATSearchBundle\Annotation\FieldId;
use ATSearchBundle\Annotation\FieldIgnored;
use ATSearchBundle\Annotation\Index;
use ATSearchBundle\Annotation\FieldInt;
use ATSearchBundle\Annotation\FieldMultiBool;
use ATSearchBundle\Annotation\FieldMultiInt;
use ATSearchBundle\Annotation\FieldMultiString;
use ATSearchBundle\Annotation\FieldString;
use ATSearchBundle\Search\ValueObject\Document;
use ATSearchBundle\Enum\FieldType;
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
        $indexPriority = 1;
        $reflectionClass = new ReflectionClass($namespace . '\\' . $className);
        $attributes = $reflectionClass->getAttributes();
        $hasIndexAttribute = false;
        foreach ($attributes as $attribute) {
            $hasIndexAttribute = $attribute->getName() === Index::class;
            if ($hasIndexAttribute) {
                $indexName = $attribute->getArguments()['name'] ?? $this->toSnakeCase($className);
                $indexPriority = $attribute->getArguments()['priority'] ?? 1;
                break;
            }
        }

        if (!$hasIndexAttribute) {
            return null;
        }
        $fields = [];
        $fieldMap = [[]];
        $hasMultiField = true;
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
                if (!$fieldMetadata) {
                    continue;
                }
                $field = $fieldMetadata->getFieldNameWithResolver();
                if ($field) {
                    $fields[] = $field;
                }
                $fieldMap[] = $fieldMetadata->getFieldNamesForMap();
                $hasMultiField = $hasMultiField || $fieldMetadata->isMulti();
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
                if (!$fieldMetadata) {
                    continue;
                }
                $field = $fieldMetadata->getFieldNameWithResolver();
                if ($field) {
                    $fields[] = $field;
                }
                $fieldMap[] = $fieldMetadata->getFieldNamesForMap();
                $hasMultiField = $hasMultiField || $fieldMetadata->isMulti();
            }
        }

        $fileBuilder = PhpFile::new()->setNamespace('ATSearchBundle\\DocumentMetadata');
        $class = $fileBuilder->createClass($documentClassName)->setFinal()
            ->addImplements(IndexDocumentInterface::class)
            ->addUse($namespace . '\\' . $className);
        if ($hasMultiField) {
            $class = $class->addUse('Doctrine\Common\Collections\ReadableCollection');
        }
        $class = $class
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

        $class->createMethod('getDefaultPriority')
            ->setReturnType('int')
            ->setDocBlock('{@inheritdoc}')
            ->append("return '$indexPriority'");

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

        return $fileBuilder;
    }

    private function getTenantIdFunc(ReflectionAttribute $attribute, ReflectionProperty|ReflectionMethod $property): ?string
    {
        if ($attribute->getName() !== 'ATSearchBundle\Annotation\TenantId') {
            return null;
        }

        return $this->getEntityPropertyValue($attribute, $property);
    }

    private function getFieldMetadata(ReflectionAttribute $attribute, ReflectionProperty|ReflectionMethod $property): ?DocumentFieldMetadata
    {
        $type = match ($attribute->getName()) {
            FieldId::class => FieldType::ID,
            FieldString::class => FieldType::STRING,
            FieldDateTime::class => FieldType::DATE,
            FieldMultiString::class => FieldType::MULTI_STRING,
            FieldMultiInt::class => FieldType::MULTI_INTEGER,
            FieldInt::class => FieldType::INTEGER,
            FieldBool::class => FieldType::BOOLEAN,
            FieldMultiBool::class => FieldType::MULTI_BOOLEAN,
            FieldIgnored::class => 'ignored',
            default => null,
        };

        if ($type === null) {
            return null;
        }

        return new DocumentFieldMetadata(
            $attribute->getArguments()['storageName'] ?? $this->toSnakeCase($property->getName()),
            $type === 'ignored' ? null : $type,
            $this->getEntityPropertyValue($attribute, $property),
            $property->getName(),
            $attribute->getArguments()['subFields'] ?? null,
            $attribute->getArguments()['fieldName'] ?? null
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
            $nameOrFunc .= '?->format(\'c\')';
        }
        $getter = '$entity->' . $nameOrFunc;
        $subFieldFunc = '';
        $subField = $attribute->getArguments()['subFields'] ?? null;
        if ($subField) {
            if (str_contains($subField, '.')) {
                $subFields = explode('.', $subField);
                foreach ($subFields as $subField) {
                    $subFieldFunc .= '?->' . $subField;
                }
            } else {
                $subFieldFunc .= '?->' . $subField;
            }
        }

        $converter = match ($attribute->getName()) {
            FieldInt::class, FieldMultiInt::class => '(int) ',
            FieldBool::class, FieldMultiBool::class => '(bool) ',
            default => '',
        };

        if (in_array($attribute->getName(), [FieldMultiString::class, FieldMultiInt::class], true)) {
            $getter .= ' instanceof ReadableCollection ? ' . $getter . '->toArray() : ' . $getter;
            $getter = 'array_map(static fn($item) =>' . $converter . '$item' . $subFieldFunc . ', ' . $getter . ')';
        } else {
            $getter = $converter . $getter . $subFieldFunc;
        }

        return $getter;
    }
}