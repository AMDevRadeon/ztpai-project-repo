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

class AuthController extends AbstractController
{
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
            return $this->json($errors, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $em->persist($user);
        $em->persist($ur);
        $em->persist($settings);
        $em->flush();

        return $this->json(['desc' => "Created new user: " . $user->getNick(), 'code' => Response::HTTP_CREATED],
                           Response::HTTP_CREATED);
    }
}
