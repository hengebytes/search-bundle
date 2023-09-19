<?php

namespace ATSearchBundle\Search\Service;

use ATSearchBundle\Search\Generator\IndexDocumentInterface;
use ATSearchBundle\Search\ValueObject\Document;
use ATSearchBundle\Event\IndexDocumentCreatedEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

readonly class DocumentGenerator
{
    /**
     * @param iterable|IndexDocumentInterface[] $indexDocuments
     * @param EntityManagerInterface $em
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        #[TaggedIterator('at_search.search.index_document')]
        private iterable $indexDocuments,
        private EntityManagerInterface $em,
        private EventDispatcherInterface $eventDispatcher
    ) {
    }

    public function generateDocument(int $id, string $entityName): Document
    {
        $entity = $this->em->getRepository($entityName)->find($id);
        $document = new Document($id, $this->getIndexName($entityName));

        if (!$entity) {
            $document->tenantId = '*';

            $this->eventDispatcher->dispatch(new IndexDocumentCreatedEvent($document, $entity));

            return $document;
        }

        $document->tenantId = $this->getTenantId($entity);
        $document->body = $this->getBody($entity);

        $this->eventDispatcher->dispatch(new IndexDocumentCreatedEvent($document, $entity));

        return $document;
    }

    private function getBody(object $entity): array
    {
        $fields = [[]];
        foreach ($this->indexDocuments as $document) {
            if ($document->getEntityClassName() !== $entity::class) {
                continue;
            }
            $fields[] = $document->getFields($entity);
        }

        return array_merge(...$fields);
    }

    public function getIndexName(string $targetEntity): string
    {
        foreach ($this->indexDocuments as $indexDocument) {
            if ($indexDocument->getEntityClassName() === $targetEntity) {
                return $indexDocument->getIndexName();
            }
        }

        return 'default';
    }

    private function getTenantId(object $entity): string
    {
        foreach ($this->indexDocuments as $indexDocument) {
            if ($indexDocument->getEntityClassName() === $entity::class) {
                return $indexDocument->getTenantId($entity);
            }
        }

        return Document::DEFAULT_TENANT_ID;
    }

}