<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\UserRole;
use App\Entity\UserSettings;
use App\Entity\Topic;
use App\Repository\UserRepository;
use App\Repository\TopicRepository;
use App\Repository\PostRepository;
use App\Service\ValidJSONStructure;
use App\Service\UniformResponse;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
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
    #[Route('api/v1/admin/user/delete', name: 'api_admin_user_delete', methods: ['DELETE'])]
    public function deleteUser(Request $req,
                               TokenInterface $sec,
                               UserRepository $repo,
                               EntityManagerInterface $em): JsonResponse
    {
        $user = $sec->getUser();
        if (!$user)
        { 
            return $this->json(UniformResponse::createInvalid('Unauthorized', Response::HTTP_UNAUTHORIZED), 
                               Response::HTTP_UNAUTHORIZED);
        }

        $payload = $req->toArray();

        $missing_key = ValidJSONStructure::checkKeys($payload, 'uid');

        // Required fields
        if ($missing_key !== NULL) 
        {
            return $this->json(UniformResponse::createInvalid("Missing $missing_key key"),
                               Response::HTTP_BAD_REQUEST);
        }

        $u = $repo->find($payload["uid"]);
        if (!$u) 
        {
            return $this->json(UniformResponse::createInvalid('User not found'),
                               Response::HTTP_BAD_REQUEST);
        }

        if ($u->getPasshash() === '-')
        {
            return $this->json(UniformResponse::createInvalid('Already deleted'),
                               Response::HTTP_BAD_REQUEST);
        }

        if ($user->getUid() === $u->getUid())
        {
            return $this->json(UniformResponse::createInvalid('Trying to delete self'),
                               Response::HTTP_BAD_REQUEST);
        }

        $nick_before = $u->getNick();

        $u->setNick('Deleted_User_' . strval(time()));
        $u->setPasshash('-');
        $u->setProvenance('');
        $u->setMotto('');
        $u->getSettings()->setDisplayEmail(false);

        $em->persist($u);
        $em->flush();

        return $this->json(UniformResponse::createValid("Deleted user: $nick_before"));
    }


    #[OA\Tag(name: 'Admin')]
    #[Route('api/v1/admin/topic/add', name: 'api_admin_topic_add', methods: ['POST'])]
    public function addTopic(Request $req,
                             TokenInterface $sec,
                             ValidatorInterface $validator,
                             EntityManagerInterface $em): JsonResponse
    {
        $user = $sec->getUser();
        if (!$user) 
        { 
            return $this->json(UniformResponse::createInvalid('Unauthorized', Response::HTTP_UNAUTHORIZED), 
                               Response::HTTP_UNAUTHORIZED);
        }

        $payload = $req->toArray();

        $missing_key = ValidJSONStructure::checkKeys($payload, 'title', 'content');

        if ($missing_key !== NULL)
        {
            return $this->json(UniformResponse::createInvalid("Missing $missing_key key"),
                               Response::HTTP_BAD_REQUEST);
        }

        $topic = (new Topic())
            ->setUid($user->getUid())
            ->setTitle(strval($payload['title']))
            ->setContent(strval($payload['content']))
            ->setUser($user);

        $errors = $validator->validate($topic);
        if (count($errors) > 0) {
            return $this->json(UniformResponse::createInvalid(
                                   "{$errors->get(0)->getPropertyPath()}: {$errors->get(0)->getMessage()}", 
                                   Response::HTTP_UNPROCESSABLE_ENTITY),
                               Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $em->persist($topic);
        $em->flush();

        return $this->json(UniformResponse::createValid("Created new topic: {$topic->getTitle()}", NULL, Response::HTTP_CREATED),
                           Response::HTTP_CREATED);
    }


    #[OA\Tag(name: 'Admin')]
    #[Route('api/v1/admin/topic/delete', name: 'api_admin_topic_delete', methods: ['DELETE'])]
    public function deleteTopic(Request $req,
                                TokenInterface $sec,
                                TopicRepository $repo,
                                ValidatorInterface $validator,
                                EntityManagerInterface $em): JsonResponse
    {
        $user = $sec->getUser();
        if (!$user) 
        { 
            return $this->json(UniformResponse::createInvalid('Unauthorized', Response::HTTP_UNAUTHORIZED), 
                               Response::HTTP_UNAUTHORIZED);
        }

        $payload = $req->toArray();

        $missing_key = ValidJSONStructure::checkKeys($payload, 'tid');

        if ($missing_key !== NULL)
        {
            return $this->json(UniformResponse::createInvalid("Missing $missing_key key"),
                               Response::HTTP_BAD_REQUEST);
        }

        $topic = $repo->find($payload['tid']);

        if (!$topic) {
            return $this->json(UniformResponse::createInvalid('Topic not found'),
                               Response::HTTP_BAD_REQUEST);
        }

        $em->remove($topic);
        $em->flush();

        return $this->json(UniformResponse::createValid('Deleted'));
    }


    #[OA\Tag(name: 'Admin')]
    #[Route('api/v1/admin/topic/edit', name: 'api_admin_topic_edit', methods: ['PATCH'])]
    public function editTopic(Request $req,
                              TokenInterface $sec,
                              TopicRepository $repo,
                              ValidatorInterface $validator,
                              EntityManagerInterface $em): JsonResponse
    {
        $user = $sec->getUser();
        if (!$user) 
        { 
            return $this->json(UniformResponse::createInvalid('Unauthorized', Response::HTTP_UNAUTHORIZED), 
                               Response::HTTP_UNAUTHORIZED);
        }

        $payload = $req->toArray();

        $missing_key = ValidJSONStructure::checkKeys($payload, 'tid');

        if ($missing_key !== NULL)
        {
            return $this->json(UniformResponse::createInvalid("Missing $missing_key key"),
                               Response::HTTP_BAD_REQUEST);
        }

        $topic = $repo->find($payload['tid']);

        if (!$topic) {
            return $this->json(UniformResponse::createInvalid('Topic not found'),
                               Response::HTTP_BAD_REQUEST);
        }

        if (isset($payload['title'])) 
        {
            $topic->setTitle($payload['title'] ?: null);
        }

        if (isset($payload['content'])) 
        {
            $topic->setContent($payload['content'] ?: null);
        }

        if (isset($payload['archived'])) 
        {
            $topic->setIsArchived($payload['archived'] ?: false);
        }

        $errors = $validator->validate($topic);
        if (count($errors) > 0) {
            return $this->json(UniformResponse::createInvalid(
                                   "{$errors->get(0)->getPropertyPath()}: {$errors->get(0)->getMessage()}", 
                                   Response::HTTP_UNPROCESSABLE_ENTITY),
                               Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $em->persist($topic);
        $em->flush();

        return $this->json(UniformResponse::createValid('Updated'));
    }

    #[OA\Tag(name: 'Admin')]
    #[Route('api/v1/admin/post/edit', name: 'api_admin_post_edit', methods: ['PATCH'])]
    public function editPost(Request $req,
                             TokenInterface $sec,
                             PostRepository $repo,
                             ValidatorInterface $validator,
                             EntityManagerInterface $em): JsonResponse
    {
        $user = $sec->getUser();
        if (!$user) 
        { 
            return $this->json(UniformResponse::createInvalid('Unauthorized', Response::HTTP_UNAUTHORIZED), 
                               Response::HTTP_UNAUTHORIZED);
        }

        $payload = $req->toArray();

        $missing_key = ValidJSONStructure::checkKeys($payload, 'pid');

        if ($missing_key !== NULL)
        {
            return $this->json(UniformResponse::createInvalid("Missing $missing_key key"),
                               Response::HTTP_BAD_REQUEST);
        }

        $post = $repo->find($payload['pid']);

        if (!$post) {
            return $this->json(UniformResponse::createInvalid('Post not found'),
                               Response::HTTP_BAD_REQUEST);
        }

        if (isset($payload['archived'])) 
        {
            $post->setIsArchived($payload['archived'] ?: false);
        }

        if (isset($payload['closed'])) 
        {
            $post->setIsClosed($payload['closed'] ?: false);
        }

        $errors = $validator->validate($post);
        if (count($errors) > 0) {
            return $this->json(UniformResponse::createInvalid(
                                   "{$errors->get(0)->getPropertyPath()}: {$errors->get(0)->getMessage()}", 
                                   Response::HTTP_UNPROCESSABLE_ENTITY),
                               Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $em->persist($post);
        $em->flush();

        return $this->json(UniformResponse::createValid('Updated'));
    }
}
