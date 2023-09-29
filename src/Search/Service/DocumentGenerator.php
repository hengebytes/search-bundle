<?php

namespace ATSearchBundle\Search\Service;

use ATSearchBundle\Search\Event\IndexDocumentCreatedEvent;
use ATSearchBundle\Search\Provider\IndexDocumentProvider;
use ATSearchBundle\Search\ValueObject\Document;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

readonly class DocumentGenerator
{
    public function __construct(
        private IndexDocumentProvider $indexDocumentProvider,
        private EntityManagerInterface $em,
        private EventDispatcherInterface $eventDispatcher
    ) {
    }

    public function generateDocument(int $id, string $className): Document
    {
        $entity = $this->em->getRepository($className)->find($id);
        $document = new Document($id, $this->getIndexName($className));

        if (!$entity) {
            $document->tenantId = '*';

            $indexDocumentCreatedEvent = new IndexDocumentCreatedEvent($document, $entity);
            $this->eventDispatcher->dispatch($indexDocumentCreatedEvent);

            return $indexDocumentCreatedEvent->document;
        }

        $document->tenantId = $this->getTenantId($entity);
        $document->body = $this->getBody($entity);

        $indexDocumentCreatedEvent = new IndexDocumentCreatedEvent($document, $entity);
        $this->eventDispatcher->dispatch($indexDocumentCreatedEvent);

        return $indexDocumentCreatedEvent->document;
    }

    private function getBody(object $entity): array
    {
        $indexDocument = $this->indexDocumentProvider->getIndexDocument($entity::class);
        if (!$indexDocument) {
            return [];
        }

        return $indexDocument->getFields($entity);
    }

    public function getIndexName(string $entityClass): string
    {
        $indexDocument = $this->indexDocumentProvider->getIndexDocument($entityClass);
        if (!$indexDocument) {
            return 'default';
        }

        return $indexDocument->getIndexName();
    }

    private function getTenantId(object $entity): string
    {
        $indexDocument = $this->indexDocumentProvider->getIndexDocument($entity::class);
        if (!$indexDocument) {
            return Document::DEFAULT_TENANT_ID;
        }

        return $indexDocument->getTenantId($entity) ?? Document::DEFAULT_TENANT_ID;
    }

}