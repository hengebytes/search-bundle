<?php

namespace ATSearchBundle\Search\EventListener;

use ATSearchBundle\Search\Provider\IndexDocumentProvider;
use ATSearchBundle\Search\Service\IndexManager;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostRemoveEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Event\PreRemoveEventArgs;
use Doctrine\ORM\Events;

#[AsDoctrineListener(event: Events::postRemove, priority: 500, connection: 'default')]
#[AsDoctrineListener(event: Events::preRemove, priority: 500, connection: 'default')]
#[AsDoctrineListener(event: Events::postUpdate, priority: 500, connection: 'default')]
#[AsDoctrineListener(event: Events::postPersist, priority: 500, connection: 'default')]
class DoctrineEventListener
{
    private array $entityIdsToRemove = [];

    public function __construct(
        private readonly IndexDocumentProvider $indexDocumentProvider,
        private readonly IndexManager $indexManager
    ) {
    }

    public function postRemove(PostRemoveEventArgs $event): void
    {
        foreach ($this->entityIdsToRemove as $key => $entityParams) {
            $object = $event->getObject();
            if ($entityParams[1] !== $object) {
                continue;
            }
            $this->indexManager->index($entityParams[0], $object::class);
            unset($this->entityIdsToRemove[$key]);
        }
    }

    public function preRemove(PreRemoveEventArgs $event): void
    {
        $entity = $event->getObject();
        $indexDocument = $this->indexDocumentProvider->getIndexDocument($entity::class);
        if (!$indexDocument) {
            return;
        }
        $this->entityIdsToRemove[] = [$indexDocument->getEntityId($entity), $entity];
    }

    public function postUpdate(PostUpdateEventArgs $event): void
    {
        $entity = $event->getObject();
        $indexDocument = $this->indexDocumentProvider->getIndexDocument($entity::class);
        if (!$indexDocument) {
            return;
        }
        $this->indexManager->index($indexDocument->getEntityId($entity), $entity::class);
    }

    public function postPersist(PostPersistEventArgs $event): void
    {
        $entity = $event->getObject();
        $indexDocument = $this->indexDocumentProvider->getIndexDocument($entity::class);
        if (!$indexDocument) {
            return;
        }
        $this->indexManager->index($indexDocument->getEntityId($entity), $entity::class);
    }
}