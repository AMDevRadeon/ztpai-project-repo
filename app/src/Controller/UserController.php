<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

use Nelmio\ApiDocBundle\Attribute\Model;
use Nelmio\ApiDocBundle\Attribute\Security;
use OpenApi\Attributes as OA;

use App\Entity\User;
use App\Entity\UserRole;
use App\Entity\UserSettings;

use Doctrine\ORM\EntityManagerInterface;

// Users
class UserController extends AbstractController 
{
    #[Route('api/users/{id}', methods: ['GET'], requirements: ['id' => '\d+'])]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: "Returns user data given by id",
        content: new OA\JsonContent(
            type: 'object',
            example: <<<EXAMPLE
            {
                "nick": "AMickiewicz",
                "email": "litwo@ojczyzno.moja",
                "provenance": null,
                "motto": null
            }
            EXAMPLE
        )
    )]
    #[OA\Tag(name: 'Users')]
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
    #[OA\Response(
        response: Response::HTTP_CREATED,
        description: "Created new user from given data",
        content: new OA\JsonContent(
            type: 'object',
            example: <<<EXAMPLE
            {
                "desc": "Account succesfully created"
            }
            EXAMPLE
        )
    )]
    #[OA\Response(
        response: Response::HTTP_BAD_REQUEST,
        description: "Essential data not provided (missing either _nick_, _email_ or _passhash_)",
        content: new OA\JsonContent(
            type: 'object',
            example: <<<EXAMPLE
            {
                "desc": "Required data values empty"
            }
            EXAMPLE
        )
    )]
    #[OA\RequestBody(
        content: new OA\JsonContent(
            type: 'object',
            example: <<<EXAMPLE
            {
                "nick": "AMickiewicz",
                "email": "litwo@ojczyzno.moja",
                "passhash": "ABCDEFGHIJKLMNOP",
                "provenance": null,
                "motto": null
            }
            EXAMPLE
        )
    )]
    #[OA\Tag(name: 'Users')]
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