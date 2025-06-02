<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\UserRole;
use App\Entity\UserSettings;
use App\Entity\Topic;
use App\Repository\UserRepository;
use App\Repository\TopicRepository;
use App\Repository\PostRepository;
use App\Service\ValidJSONStructureService;
use App\Service\UniformResponseService;
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
    #[OA\Delete(
        description: "Delete specified user's account",
        summary: "Requires JWT of user with administrative status",
        security: [
            [
                'jwt' => []
            ]
        ]
    )]
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
        description: <<<DESC
            uid was ommited in request body,
            user not found,
            user was already deleted,
            user tried to delete their own account
        DESC,
        content: new OA\JsonContent(
            type: 'object',
            example: <<<EXAMPLE
                {
                    "desc": "User not found",
                    "code": "400"
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
        content: [new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                required: [
                    'uid',
                ],
                properties: [
                    new OA\Property(
                        property: 'uid',
                        type: 'integer',
                        description: "ID of user to delete"
                    )
                ]
            ),
            example: [
                'uid' => 10
            ]
        )]
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
            return $this->json(UniformResponseService::createInvalid('Unauthorized', Response::HTTP_UNAUTHORIZED), 
                               Response::HTTP_UNAUTHORIZED);
        }

        $payload = $req->toArray();

        $missing_key = ValidJSONStructureService::checkKeys($payload, 'uid');

        // Required fields
        if ($missing_key !== NULL) 
        {
            return $this->json(UniformResponseService::createInvalid("Missing $missing_key key"),
                               Response::HTTP_BAD_REQUEST);
        }

        $u = $repo->find($payload["uid"]);
        if (!$u) 
        {
            return $this->json(UniformResponseService::createInvalid('User not found'),
                               Response::HTTP_BAD_REQUEST);
        }

        if ($u->getPasshash() === '-')
        {
            return $this->json(UniformResponseService::createInvalid('Already deleted'),
                               Response::HTTP_BAD_REQUEST);
        }

        if ($user->getUid() === $u->getUid())
        {
            return $this->json(UniformResponseService::createInvalid('Trying to delete self'),
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

        return $this->json(UniformResponseService::createValid("Deleted user: $nick_before"));
    }


    #[OA\Post(
        description: "Add new topic to discuss",
        summary: "Requires JWT of user with administrative status",
        security: [
            [
                'jwt' => []
            ]
        ]
    )]
    #[OA\Response(
        response: Response::HTTP_CREATED,
        description: "Successfully created new topic",
        content: new OA\JsonContent(
            type: 'object',
            example: <<<EXAMPLE
                {
                    "desc": "Created new topic: Interesting title",
                    "code": "201"
                }
            EXAMPLE
        )
    )]
    #[OA\Response(
        response: Response::HTTP_BAD_REQUEST,
        description: 'title and/or content was ommited in request body',
        content: new OA\JsonContent(
            type: 'object',
            example: <<<EXAMPLE
                {
                    "desc": "Missing content key",
                    "code": "400"
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
        content: [new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                required: [
                    'title',
                    'content'
                ],
                properties: [
                    new OA\Property(
                        property: 'title',
                        type: 'string',
                        description: "Title of new topic"
                    ),
                    new OA\Property(
                        property: 'content',
                        type: 'string',
                        description: "Description of what this topic is about"
                    )
                ]
            ),
            example: [
                'title' => 'Interesting title',
                'content' => 'Interesting content'
            ]
        )]
    )]
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
            return $this->json(UniformResponseService::createInvalid('Unauthorized', Response::HTTP_UNAUTHORIZED), 
                               Response::HTTP_UNAUTHORIZED);
        }

        $payload = $req->toArray();

        $missing_key = ValidJSONStructureService::checkKeys($payload, 'title', 'content');

        if ($missing_key !== NULL)
        {
            return $this->json(UniformResponseService::createInvalid("Missing $missing_key key"),
                               Response::HTTP_BAD_REQUEST);
        }

        $topic = (new Topic())
            ->setUid($user->getUid())
            ->setTitle(strval($payload['title']))
            ->setContent(strval($payload['content']))
            ->setUser($user);

        $errors = $validator->validate($topic);
        if (count($errors) > 0) {
            return $this->json(UniformResponseService::createInvalid(
                                   "{$errors->get(0)->getPropertyPath()}: {$errors->get(0)->getMessage()}", 
                                   Response::HTTP_UNPROCESSABLE_ENTITY),
                               Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $em->persist($topic);
        $em->flush();

        return $this->json(UniformResponseService::createValid("Created new topic: {$topic->getTitle()}", NULL, Response::HTTP_CREATED),
                           Response::HTTP_CREATED);
    }


    #[OA\Delete(
        description: "Delete permanently topic of discussion",
        summary: "Requires JWT of user with administrative status",
        security: [
            [
                'jwt' => []
            ]
        ]
    )]
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
        description: <<<DESC
            tid was ommited in request body,
            topic not found
        DESC,
        content: new OA\JsonContent(
            type: 'object',
            example: <<<EXAMPLE
                {
                    "desc": "Topic not found",
                    "code": "400"
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
        content: [new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                required: [
                    'tid',
                ],
                properties: [
                    new OA\Property(
                        property: 'tid',
                        type: 'integer',
                        description: "ID of topic to delete"
                    )
                ]
            ),
            example: [
                'tid' => 127
            ]
        )]
    )]
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
            return $this->json(UniformResponseService::createInvalid('Unauthorized', Response::HTTP_UNAUTHORIZED), 
                               Response::HTTP_UNAUTHORIZED);
        }

        $payload = $req->toArray();

        $missing_key = ValidJSONStructureService::checkKeys($payload, 'tid');

        if ($missing_key !== NULL)
        {
            return $this->json(UniformResponseService::createInvalid("Missing $missing_key key"),
                               Response::HTTP_BAD_REQUEST);
        }

        $topic = $repo->find($payload['tid']);

        if (!$topic) {
            return $this->json(UniformResponseService::createInvalid('Topic not found'),
                               Response::HTTP_BAD_REQUEST);
        }

        $em->remove($topic);
        $em->flush();

        return $this->json(UniformResponseService::createValid('Deleted'));
    }


    #[OA\Patch(
        description: "Edit existing topic",
        summary: "Requires JWT of user with administrative status",
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
                    "code": "200"
                }
            EXAMPLE
        )
    )]
    #[OA\Response(
        response: Response::HTTP_BAD_REQUEST,
        description: <<<DESC
            tid was ommited in request body,
            topic not found
        DESC,
        content: new OA\JsonContent(
            type: 'object',
            example: <<<EXAMPLE
                {
                    "desc": "Topic not found",
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
    #[OA\Response(
        response: Response::HTTP_UNPROCESSABLE_ENTITY,
        description: "Constraints not met",
        content: new OA\JsonContent(
            type: 'object',
            example: <<<EXAMPLE
                {
                    "desc": "topic: This value is too long. It should have 512 characters or less.",
                    "code": "422"
                }
            EXAMPLE
        )
    )]
    #[OA\RequestBody(
        required: true,
        content: [new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                required: [
                    'tid',
                ],
                properties: [
                    new OA\Property(
                        property: 'tid',
                        type: 'integer',
                        description: "ID of topic to edit"
                    ),
                    new OA\Property(
                        property: 'title',
                        type: 'string',
                        description: "New title of topic"
                    ),
                    new OA\Property(
                        property: 'content',
                        type: 'string',
                        description: "New description of topic"
                    ),
                    new OA\Property(
                        property: 'archived',
                        type: 'boolean',
                        description: "Whether topic is archived or not"
                    )
                ]
            ),
            example: [
                'tid' => 127,
                'title' => 'Interesting title',
                'content' => 'Interesting content',
                'archived' => true
            ]
        )]
    )]
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
            return $this->json(UniformResponseService::createInvalid('Unauthorized', Response::HTTP_UNAUTHORIZED), 
                               Response::HTTP_UNAUTHORIZED);
        }

        $payload = $req->toArray();

        $missing_key = ValidJSONStructureService::checkKeys($payload, 'tid');

        if ($missing_key !== NULL)
        {
            return $this->json(UniformResponseService::createInvalid("Missing $missing_key key"),
                               Response::HTTP_BAD_REQUEST);
        }

        $topic = $repo->find($payload['tid']);

        if (!$topic) {
            return $this->json(UniformResponseService::createInvalid('Topic not found'),
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
            return $this->json(UniformResponseService::createInvalid(
                                   "{$errors->get(0)->getPropertyPath()}: {$errors->get(0)->getMessage()}", 
                                   Response::HTTP_UNPROCESSABLE_ENTITY),
                               Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $em->persist($topic);
        $em->flush();

        return $this->json(UniformResponseService::createValid('Updated'));
    }


    #[OA\Patch(
        description: "Edit status of provided post",
        summary: "Requires JWT of user with administrative status",
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
                    "code": "200"
                }
            EXAMPLE
        )
    )]
    #[OA\Response(
        response: Response::HTTP_BAD_REQUEST,
        description: <<<DESC
            pid was ommited in request body,
            post not found
        DESC,
        content: new OA\JsonContent(
            type: 'object',
            example: <<<EXAMPLE
                {
                    "desc": "Post not found",
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
    #[OA\Response(
        response: Response::HTTP_UNPROCESSABLE_ENTITY,
        description: "Constraints not met",
        content: new OA\JsonContent(
            type: 'object',
            example: <<<EXAMPLE
                {
                    "desc": "topic: This value is too long. It should have 512 characters or less.",
                    "code": "422"
                }
            EXAMPLE
        )
    )]
    #[OA\RequestBody(
        required: true,
        content: [new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                required: [
                    'pid',
                ],
                properties: [
                    new OA\Property(
                        property: 'pid',
                        type: 'integer',
                        description: "ID of post to edit"
                    ),
                    new OA\Property(
                        property: 'archived',
                        type: 'boolean',
                        description: "Whether post is archived or not"
                    ),
                    new OA\Property(
                        property: 'closed',
                        type: 'boolean',
                        description: "Whether post is closed or not"
                    )
                ]
            ),
            example: [
                'tid' => 127,
                'title' => 'Interesting title',
                'content' => 'Interesting content',
                'archived' => true
            ]
        )]
    )]
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
            return $this->json(UniformResponseService::createInvalid('Unauthorized', Response::HTTP_UNAUTHORIZED), 
                               Response::HTTP_UNAUTHORIZED);
        }

        $payload = $req->toArray();

        $missing_key = ValidJSONStructureService::checkKeys($payload, 'pid');

        if ($missing_key !== NULL)
        {
            return $this->json(UniformResponseService::createInvalid("Missing $missing_key key"),
                               Response::HTTP_BAD_REQUEST);
        }

        $post = $repo->find($payload['pid']);

        if (!$post) {
            return $this->json(UniformResponseService::createInvalid('Post not found'),
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
            return $this->json(UniformResponseService::createInvalid(
                                   "{$errors->get(0)->getPropertyPath()}: {$errors->get(0)->getMessage()}", 
                                   Response::HTTP_UNPROCESSABLE_ENTITY),
                               Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $em->persist($post);
        $em->flush();

        return $this->json(UniformResponseService::createValid('Updated'));
    }
}
