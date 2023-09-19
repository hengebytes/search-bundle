<?php

namespace ATSearchBundle\EventSubscriber;

use ATSearchBundle\Search\Generator\IndexExtractor;
use ATSearchBundle\Event\IndexDocumentCreatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

readonly class IndexSubscriber implements EventSubscriberInterface
{
    public function __construct(private IndexExtractor $indexExtractor)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            IndexDocumentCreatedEvent::class => ['onIndexDocumentCreated', -255],
        ];
    }

    public function onIndexDocumentCreated(IndexDocumentCreatedEvent $event): void
    {
        if (!$event->entity) {
            return;
        }
        $additionalFields = $this->indexExtractor->extract($event->entity);
        if (!$additionalFields) {
            return;
        }
        $event->document->body = array_merge($event->document->body, $additionalFields);
    }
}