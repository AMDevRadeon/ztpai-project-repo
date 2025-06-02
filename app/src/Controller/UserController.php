<?php
namespace App\Controller;

use App\Repository\UserRepository;
use App\Service\ValidJSONStructureService;
use App\Service\UniformResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

use OpenApi\Attributes as OA;

trait ValidateRequest
{
    protected function validate(Request &$req, ...$keys): array
    {
        $payload = $req->toArray();
        $missing_key = ValidJSONStructureService::checkKeys($payload, ...$keys);

        if ($missing_key !== NULL)
        {
            return [
                'payload' => $payload,
                'error' => $this->json(
                    UniformResponseService::createInvalid(
                        "Missing $missing_key key"
                    ),
                    Response::HTTP_BAD_REQUEST
                )
                ];
        }

        return [
            'payload' => $payload,
            'error' => NULL
        ];
    }
}

class UserController extends AbstractController
{
    use ValidateRequest;

    #[OA\Post(
        description: "Get publicly available information about the user"
    )]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: "Returns data about user",
        content: new OA\JsonContent(
            type: 'object',
            example: 
                [
                    "desc" => "Response",
                    "status" => 200,
                    "value" => [
                        "nick" => "alfa",
                        "motto" => "Jest jak jest",
                        "provenance" => "Bywa"
                    ]
                ]
        )
    )]
    #[OA\Response(
        response: Response::HTTP_BAD_REQUEST,
        description: "No data about user with provided UID",
        content: new OA\JsonContent(
            type: 'object',
            example:
                [
                    "desc" => "User not found",
                    "status" => 400,
                ]
        )
    )]
    #[OA\RequestBody(
        required: true,
        content: [new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                required: [
                    'uid'
                ],
                properties: [
                    new OA\Property(
                        property: 'uid',
                        type: 'integer',
                        description: "User ID"
                    )
                ]
            ),
            example: [
                "uid" => 1
            ]
        )]
    )]
    #[OA\Tag(name: 'Content')]
    #[Route('/api/v1/user/get', name: 'api_user_get', methods: ['POST'])]
    public function publicProfile(Request $req,
                                  UserRepository $repo): JsonResponse
    {
        $payload = $this->validate($req, 'uid');
        if ($payload['error'] !== NULL)
        {
            return $payload['error'];
        }

        $u = $repo->find($payload['payload']['uid']);
        if (!$u) {
            return $this->json(UniformResponseService::createInvalid('User not found'),
                               Response::HTTP_BAD_REQUEST);
        }

        $data = [
            'nick'       => $u->getNick(),
            'motto'      => $u->getMotto(),
            'provenance' => $u->getProvenance(),
        ];

        // Test if we should include email address with the response
        $viewer = $this->getUser();
        $same   = $viewer && $viewer->getUserIdentifier() === $u->getEmail();
        if ($same || $u->getSettings()->isDisplayEmail()) {
            $data['email'] = $u->getEmail();
        }

        return $this->json(UniformResponseService::createValid('Response', $data));
    }


    #[OA\Patch(
        description: "Alter certain characteristics of account",
        summary: "Requires JWT from user or higher",
        security: [
            [
                'jwt' => []
            ]
        ]
    )]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: "Data successfully commited to database",
        content: new OA\JsonContent(
            type: 'object',
            example: <<<EXAMPLE
                {
                    "desc": "Updated",
                    "code": "400"
                }
            EXAMPLE
        )
    )]
    #[OA\Response(
        response: Response::HTTP_UNAUTHORIZED,
        description: "User did not log in",
        content: new OA\JsonContent(
            type: 'object',
            example: <<<EXAMPLE
                {
                    "desc": "Unauthorized",
                    "code": "401"
                }
            EXAMPLE
        )
    )]
    #[OA\RequestBody(
        required: true,
        content: [new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                properties: [
                    new OA\Property(
                        property: 'motto',
                        type: 'string',
                        description: "If provided, change account motto"
                    ),
                    new OA\Property(
                        property: 'provenance',
                        type: 'string',
                        description: "If provided, change account provenance"
                    ),
                    new OA\Property(
                        property: 'password',
                        type: 'string',
                        description: "If provided, change account password"
                    ),
                ]
            ),
            example:[
                "motto" => "new_motto",
                "provenance" => "new_provenance",
                "password" => "new_password"
            ]
        )]
    )]
    #[OA\Tag(name: 'User')]
    #[Route('/api/v1/user/me', name: 'api_user_me', methods: ['PATCH'])]
    public function updateMe(Request $req,
                             TokenInterface $sec,
                             EntityManagerInterface $em,
                             UserPasswordHasherInterface $hasher): JsonResponse 
    {
        $user = $sec->getUser();
        if (!$user) 
        { 
            return $this->json(
                UniformResponseService::createInvalid('Unauthorized', Response::HTTP_UNAUTHORIZED),
                Response::HTTP_UNAUTHORIZED);
        }

        $payload = json_decode($req->getContent(), true);

        if (isset($payload['motto'])) 
        {
            $user->setMotto($payload['motto'] ?: null);
        }

        if (isset($payload['provenance'])) 
        {
            $user->setProvenance($payload['provenance'] ?: null);
        }

        if (!empty($payload['password']))
        {
            $user->setPasshash($hasher->hashPassword($user, $payload['password']));
        }

        $em->persist($user);
        $em->flush();

        return $this->json(UniformResponseService::createValid('Updated'));
    }
}
