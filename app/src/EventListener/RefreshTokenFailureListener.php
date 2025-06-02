<?php
namespace App\EventListener;

use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Gesdinet\JWTRefreshTokenBundle\Event\RefreshAuthenticationFailureEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Service\UniformResponseService;


class RefreshTokenFailureListener
{
    /**
     * @param RefreshAuthenticationFailureEvent $event
     */
    public function onRefreshTokenFailure(RefreshAuthenticationFailureEvent $event)
    {
        $data = json_encode(UniformResponseService::createInvalid('Unauthorized - Could not authenticate refresh token', Response::HTTP_UNAUTHORIZED));

        $response = new Response();
        $response->setContent($data);
        $response->headers->set('Content-Type', 'application/json; charset=utf-8');
        $response->setStatusCode(Response::HTTP_UNAUTHORIZED);

        $event->setResponse($response);
    }
}