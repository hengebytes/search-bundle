<?php

namespace ATSearchBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;
use ATSearchBundle\Elastic\ValueObject\Document;

class IndexDocumentCreatedEvent extends Event
{
    public function __construct(public readonly Document $document, public readonly ?object $entity)
    {
    }

}