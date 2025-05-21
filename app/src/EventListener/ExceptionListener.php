<?php
namespace App\EventListener;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

class ExceptionListener
{
    public function __invoke(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        $jsonMessage = json_encode(
            [
                "desc" => $exception->getMessage(),
                "code" => Response::HTTP_INTERNAL_SERVER_ERROR
            ]
        );

        $response = new Response();
        $response->setContent($jsonMessage);

        if ($exception instanceof HttpExceptionInterface) {
            $response->setStatusCode($exception->getStatusCode());
            $response->headers->replace($exception->getHeaders());
        } else {
            $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $response->headers->set('Content-Type', 'application/json; charset=utf-8');

        $event->setResponse($response);
    }
}