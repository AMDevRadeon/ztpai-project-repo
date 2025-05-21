<?php
namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
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
            return $this->json(['error' => 'User not found'], Response::HTTP_BAD_REQUEST);
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
                             Security $sec,
                             UserPasswordHasherInterface $hasher): JsonResponse 
    {
        $user = $sec->getUser();
        if (!$user) 
        { 
            return $this->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
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

        if (!empty($payload['passhash']))
        {
            $user->setPasshash($hasher->hashPassword($user, $payload['passhash']));
        }

        $this->getDoctrine()->getManager()->flush();
        return $this->json(['status' => 'updated']);
    }
}
