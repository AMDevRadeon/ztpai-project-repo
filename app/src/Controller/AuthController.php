<?php
namespace App\Controller;

use App\Entity\User;
use App\Entity\UserRole;
use App\Entity\UserSettings;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpFoundation\Response;

use OpenApi\Attributes as OA;

class AuthController extends AbstractController
{
    #[OA\Response(
        response: Response::HTTP_OK,
        description: "User successfully created",
        content: new OA\JsonContent(
            type: 'object',
            example: <<<EXAMPLE
                {
                    "desc": "Created new user: nick",
                    "code": "200"
                }
            EXAMPLE
        )
    )]
    #[OA\Response(
        response: Response::HTTP_BAD_REQUEST,
        description: "Some fields missing",
        content: new OA\JsonContent(
            type: 'object',
            example: <<<EXAMPLE
                {
                    "desc": "Missing password",
                    "code": "401"
                }
            EXAMPLE
        )
    )]
    #[OA\Response(
        response: Response::HTTP_UNPROCESSABLE_ENTITY,
        description: "Constraints not met",
        content: new OA\JsonContent(
            type: 'object',
            example: <<<EXAMPLE
                {
                    "desc": "nick: This value is too long. It should have 64 characters or less.",
                    "code": "422"
                }
            EXAMPLE
        )
    )]    
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            type: Object::class,
            example: [
                "nick" => "user",
                "email" => "email@test.com",
                "password" => "password"
            ]
        )
    )]
    #[OA\Tag(name: 'API')]
    #[Route('/api/register', name: 'api_register', methods: ['POST'])]
    public function register(Request $request,
                             EntityManagerInterface $em,
                             UserPasswordHasherInterface $hasher,
                             ValidatorInterface $validator): JsonResponse 
    {
        $payload = $request->toArray();

        // Required fields
        foreach (['nick', 'email', 'password'] as $field) {
            if (empty($payload[$field])) {
                return $this->json(["desc" => "Missing $field", 'code' => Response::HTTP_BAD_REQUEST],
                                   Response::HTTP_BAD_REQUEST);
            }
        }

        $user = (new User())
            ->setNick($payload['nick'])
            ->setEmail($payload['email'])
            ->setPasshash(
                $hasher->hashPassword(new User(), $payload['password'])
            )
            ->setProvenance($payload['provenance'] ?? null)
            ->setMotto($payload['motto'] ?? null);

        $ur = (new UserRole())
            ->setRole(1)
            ->setUser($user);

        $settings = (new UserSettings())
            ->setDisplayEmail($payload['display_email'] ?? false)
            ->setUser($user);

        $errors = $validator->validate($user);
        if (count($errors) > 0) {
            return $this->json(['desc' => $errors->get(0)->getPropertyPath() . ': ' . $errors->get(0)->getMessage(), 'code' => Response::HTTP_UNPROCESSABLE_ENTITY],
                               Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $em->persist($user);
        $em->persist($ur);
        $em->persist($settings);
        $em->flush();

        return $this->json(['desc' => "Created new user: " . $user->getNick(), 'code' => Response::HTTP_CREATED],
                           Response::HTTP_CREATED);
    }
}
