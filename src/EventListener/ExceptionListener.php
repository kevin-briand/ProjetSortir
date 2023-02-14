<?php

namespace App\EventListener;

use JMS\Serializer\SerializerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

#[AsEventListener]
class ExceptionListener
{
    public function __construct(
        private readonly SerializerInterface $serializer
    )
    {
    }

    public function __invoke(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        $response = new Response();
        $response->setContent($this->serializer->serialize(
            [
                'errors' => new ErrorRepresentation(
                    $exception->getMessage(),
                ),
            ],
            'json'
        ));

        if ($exception instanceof HttpExceptionInterface) {
            $response->setStatusCode($exception->getStatusCode());
            $response->headers->replace($exception->getHeaders());
        } else {
            $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
            $response->headers->replace(['Content-Type' => 'application/json']);
        }

        $event->setResponse($response);
    }
}
