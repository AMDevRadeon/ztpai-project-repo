<?php
namespace App\EventListener;

use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationFailureEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTNotFoundEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTInvalidEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Response\JWTAuthenticationFailureResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Service\UniformResponseService;

class TokenInvalidListener
{
    /**
     * @param JWTInvalidEvent $event
     */
    public function onJWTInvalid(JWTInvalidEvent $event)
    {
        $data = json_encode(UniformResponseService::createInvalid('Unauthorized - JWT invalid', Response::HTTP_UNAUTHORIZED));

        $response = new Response();
        $response->setContent($data);
        $response->headers->set('Content-Type', 'application/json; charset=utf-8');
        $response->setStatusCode(Response::HTTP_UNAUTHORIZED);

        $event->setResponse($response);
    }
}