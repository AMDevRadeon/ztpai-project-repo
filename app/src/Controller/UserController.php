<?php
namespace App\Controller;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

use OpenApi\Attributes as OA;


class UserController extends AbstractController
{
    #[OA\Response(
        response: Response::HTTP_OK,
        description: "Returns data about user",
        content: new OA\JsonContent(
            type: 'object',
            example: <<<EXAMPLE
                {
                    "uid": "1",
                    "nick": "alfa",
                    "motto": "Jest jak jest"
                }
            EXAMPLE
        )
    )]
    #[OA\Response(
        response: Response::HTTP_BAD_REQUEST,
        description: "No data about user with provided UID",
        content: new OA\JsonContent(
            type: 'object',
        )
    )]
    #[OA\Tag(name: 'API')]
    #[Route('/api/user/{uid}', name: 'api_user_public', methods: ['GET'])]
    public function publicProfile(int $uid, UserRepository $repo): JsonResponse
    {
        $u = $repo->find($uid);
        if (!$u) {
            return $this->json(['desc' => 'User not found', 'code' => Response::HTTP_BAD_REQUEST],
                                Response::HTTP_BAD_REQUEST);
        }

        $data = [
            'uid'        => $u->getUid(),
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

        return $this->json($data);
    }



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
        content: new OA\JsonContent(
            type: 'object',
            example:[
                "motto" => "new_motto",
                "provenance" => "new_provenance",
                "password" => "new_password"
            ]
        )
    )]
    #[OA\Tag(name: 'API')]
    #[Route('/api/user/me', name: 'api_user_me', methods: ['PATCH'])]
    public function updateMe(Request $req,
                             TokenInterface $sec,
                             EntityManagerInterface $em,
                             UserPasswordHasherInterface $hasher): JsonResponse 
    {
        $user = $sec->getUser();
        if (!$user) 
        { 
            return $this->json(['desc' => 'Unauthorized', 'code' => Response::HTTP_UNAUTHORIZED],
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

        return $this->json(['desc' => 'Updated', 'code' => Response::HTTP_OK]);
    }
}
