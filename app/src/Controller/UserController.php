<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

use App\Entity\User;
use App\Entity\UserRole;
use App\Entity\UserSettings;

use Doctrine\ORM\EntityManagerInterface;

// Users
class UserController extends AbstractController 
{
    #[Route('api/users/{id}', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(EntityManagerInterface $entityManager, int $id): Response
    {
        $user = $entityManager->getRepository(User::class)->find($id);

        // var_dump($user);

        // $users = [
        //     '1' => [
        //         'name' => "Adam Mickiewicz",
        //         'email' => "litwo@ojczyzno.moja",
        //     ],
        //     '2' => [
        //         'name' => "Juliusz Słowacki",
        //         'email' => "ele@bele.mele",
        //     ]
        // ];

        $response = new Response();

        if ($user) {
            $response->setContent(json_encode($user));
            $response->setStatusCode(Response::HTTP_OK);
    
            $response->headers->set('Content-Type', 'application/json');
        }
        else {
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
        }
        
        return $response;
    }
}