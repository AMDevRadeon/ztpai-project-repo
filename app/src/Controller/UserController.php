<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

// Users
class UserController extends AbstractController 
{
    #[Route('api/users/{id}', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(string $id): Response
    {
        $users = [
            '1' => [
                'name' => "Adam Mickiewicz",
                'email' => "litwo@ojczyzno.moja",
            ],
            '2' => [
                'name' => "Juliusz Słowacki",
                'email' => "ele@bele.mele",
            ]
        ];

        $response = new Response();

        if (array_key_exists($id, $users)) {
            $response->setContent(json_encode($users[$id]));
            $response->setStatusCode(Response::HTTP_OK);
    
            $response->headers->set('Content-Type', 'application/json');
        }
        else {
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
        }
        
        return $response;
    }
}