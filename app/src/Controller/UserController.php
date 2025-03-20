<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class UserController extends AbstractController 
{
    #[Route('api/users/{id}', methods: ['GET'])]
    public function show(int $id): Response
    {
        $response = new Response();

        $response->setContent("<div>Hello ".$id."</div>");
        $response->setStatusCode(Response::HTTP_OK);

        $response->headers->set('Content-Type', 'text/html');
        
        return $response;
    }
}