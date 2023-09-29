<?php

namespace ATSearchBundle\Search\Event;

use ATSearchBundle\Search\ValueObject\Document;
use Symfony\Contracts\EventDispatcher\Event;

class IndexDocumentCreatedEvent extends Event
{
    public function __construct(public readonly Document $document, public readonly ?object $entity)
    {
    }

}