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

class UserController extends AbstractController
{
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
