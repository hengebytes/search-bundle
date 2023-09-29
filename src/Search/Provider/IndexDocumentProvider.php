<?php

namespace ATSearchBundle\Search\Provider;

use ATSearchBundle\Search\Generator\IndexDocumentInterface;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;

final readonly class IndexDocumentProvider
{
    /**
     * @param iterable<IndexDocumentInterface> $indexDocuments
     */
    public function __construct(
        #[TaggedIterator('at_search.search.index_document')]
        private iterable $indexDocuments,
    ) {
    }

    public function getIndexDocument(string $entityClass): ?IndexDocumentInterface
    {
        foreach ($this->indexDocuments as $indexDocument) {
            if (
                $indexDocument->getEntityClassName() !== $entityClass
                && !is_subclass_of($entityClass, $indexDocument->getEntityClassName())
            ) {
                continue;
            }
            return $indexDocument;
        }
        return null;
    }


    public function getEntityClassNameByIndex(string $index): ?string
    {
        foreach ($this->indexDocuments as $indexDocument) {
            if ($indexDocument->getIndexName() === $index) {
                return $indexDocument->getEntityClassName();
            }
        }

        return null;
    }

    public function getAvailableEntityClasses(): array
    {
        $entityClasses = [];
        foreach ($this->indexDocuments as $indexDocument) {
            $entityClasses[] = $indexDocument->getEntityClassName();
        }

        return $entityClasses;
    }

    public function getESFieldName(string $fieldName): ?string
    {
        foreach ($this->indexDocuments as $indexDocument) {
            $resolvedName = $indexDocument->getESFieldName($fieldName);
            if ($resolvedName) {
                return $resolvedName;
            }
        }

        return null;
    }

}