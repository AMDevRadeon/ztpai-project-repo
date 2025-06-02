<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Authorization\AccessDeniedHandlerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Service\UniformResponseService;

class AccessDeniedHandler implements AccessDeniedHandlerInterface
{
    public function handle(Request $request, AccessDeniedException $accessDeniedException): ?Response
    {
        $data = json_encode(UniformResponseService::createInvalid('Unauthorized', Response::HTTP_UNAUTHORIZED));

        $response = new Response();
        $response->setContent($data);
        $response->headers->set('Content-Type', 'application/json; charset=utf-8');
        $response->setStatusCode(Response::HTTP_FORBIDDEN);

        return new Response($content, Response::HTTP_FORBIDDEN);
    }
}