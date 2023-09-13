<?php

namespace ATernovtsii\SearchBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;
use ATernovtsii\SearchBundle\Elastic\ValueObject\Document;

class IndexCreateEvent extends Event
{
    public function __construct(public readonly Document $document, public readonly ?object $entity)
    {
    }

}