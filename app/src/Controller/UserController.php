<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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

    #[Route('api/users/add', methods: ['POST'])]
    public function add(EntityManagerInterface $entityManager, Request $request): Response
    {
        $user = new User();

        $data = $request->getPayload();

        if ($data->get("nick") === null ||
            $data->get("email") === null ||
            $data->get("passhash") === null)
        {
            return new Response(
                json_encode(
                    [
                        "desc" => "Required data values empty"
                    ]
                ),
                Response::HTTP_BAD_REQUEST);
        }

        $user->setNick($data->get("nick"));
        $user->setEmail($data->get("email"));
        $user->setPasshash($data->get("passhash"));
        $user->setProvenance($data->get("provenance"));
        $user->setMotto($data->get("motto"));

        $entityManager->persist($user);

        $entityManager->flush();

        return new Response(
            json_encode(
                [
                    "desc" => "Account succesfully created"
                ]
            ),
            Response::HTTP_CREATED);
    }
}