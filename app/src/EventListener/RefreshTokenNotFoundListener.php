<?php
namespace App\EventListener;

use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Gesdinet\JWTRefreshTokenBundle\Event\RefreshAuthenticationFailureEvent;
use Gesdinet\JWTRefreshTokenBundle\Event\RefreshTokenNotFoundEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Service\UniformResponseService;


class RefreshTokenNotFoundListener
{
    /**
     * @param RefreshTokenNotFoundEvent $event
     */
    public function onRefreshTokenNotFound(RefreshTokenNotFoundEvent $event)
    {
        $data = json_encode(UniformResponseService::createInvalid('Unauthorized - Refresh token not found', Response::HTTP_UNAUTHORIZED));

        $response = new Response();
        $response->setContent($data);
        $response->headers->set('Content-Type', 'application/json; charset=utf-8');
        $response->setStatusCode(Response::HTTP_UNAUTHORIZED);

        $event->setResponse($response);
    }
}