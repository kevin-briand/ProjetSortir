<?php

namespace App\EventListener;

use JMS\Serializer\Annotation as Serializer;

final class ErrorRepresentation
{
    public function __construct(
        #[Serializer\Expose, Serializer\Type('string')]
        public readonly string $message
    )
    {
    }
}