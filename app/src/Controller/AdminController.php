<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\UserRole;
use App\Entity\UserSettings;
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


final class AdminController extends AbstractController
{
    #[OA\Response(
        response: Response::HTTP_OK,
        description: "User deleted",
        content: new OA\JsonContent(
            type: 'object',
            example: <<<EXAMPLE
                {
                    "desc": "Deleted",
                    "code": "200"
                }
            EXAMPLE
        )
    )]
    #[OA\Response(
        response: Response::HTTP_BAD_REQUEST,
        description: "Invalid request made in this context",
        content: new OA\JsonContent(
            type: 'object',
            example: <<<EXAMPLE
                {
                    "desc": "User not found",
                    "code": "401"
                }
            EXAMPLE
        )
    )]
    #[OA\Response(
        response: Response::HTTP_UNAUTHORIZED,
        description: "User not authorized",
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
            type: Object::class,
            example: [
                "uid" => "10"
            ]
        )
    )]
    #[OA\Tag(name: 'Admin')]
    #[Route('api/admin/delete', name: 'api_admin_delete', methods: ['DELETE'])]
    public function deleteUser(Request $req,
                               TokenInterface $sec,
                               UserRepository $repo,
                               EntityManagerInterface $em): JsonResponse
    {
        $user = $sec->getUser();
        if (!$user) 
        { 
            return $this->json(['desc' => 'Unauthorized', 'code' => Response::HTTP_UNAUTHORIZED], 
                               Response::HTTP_UNAUTHORIZED);
        }

        $payload = $req->toArray();

        // Required fields
        if (empty($payload["uid"])) 
        {
            return $this->json(["desc" => "Missing $field", 'code' => Response::HTTP_BAD_REQUEST],
                               Response::HTTP_BAD_REQUEST);
        }

        $u = $repo->find($payload["uid"]);
        if (!$u) 
        {
            return $this->json(['desc' => 'User not found', 'code' => Response::HTTP_BAD_REQUEST],
                               Response::HTTP_BAD_REQUEST);
        }

        if ($u->getPasshash() === '-')
        {
            return $this->json(['desc' => 'Already deleted', 'code' => Response::HTTP_BAD_REQUEST],
                               Response::HTTP_BAD_REQUEST);
        }

        if ($user->getUid() === $u->getUid())
        {
            return $this->json(['desc' => 'Trying to delete self', 'code' => Response::HTTP_BAD_REQUEST],
                               Response::HTTP_BAD_REQUEST);
        }

        $u->setNick('Deleted_User_' . strval(time()));
        $u->setPasshash('-');
        $u->setProvenance('');
        $u->setMotto('');
        $u->getSettings()->setDisplayEmail(false);

        $em->persist($u);
        $em->flush();

        return $this->json(['desc' => 'Deleted', 'code' => Response::HTTP_OK]);
    }
}
