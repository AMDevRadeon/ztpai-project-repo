<?php
namespace App\EventListener;

use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationFailureEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTNotFoundEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTExpiredEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Response\JWTAuthenticationFailureResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Service\UniformResponseService;


class TokenExpiredListener
{
    /**
     * @param JWTExpiredEvent $event
     */
    public function onJWTExpired(JWTExpiredEvent $event)
    {
        $data = json_encode(UniformResponseService::createInvalid('Unauthorized - JWT expired', Response::HTTP_UNAUTHORIZED));

        $response = new Response();
        $response->setContent($data);
        $response->headers->set('Content-Type', 'application/json; charset=utf-8');
        $response->setStatusCode(Response::HTTP_UNAUTHORIZED);

        $event->setResponse($response);
    }
}