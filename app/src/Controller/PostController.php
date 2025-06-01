<?php

namespace App\Controller;

use App\Entity\Topic;
use App\Entity\Post;
use App\Entity\User;
use App\Database\PostDatabaseQueries;
use App\Service\ValidJSONStructure;
use App\Service\UniformResponse;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Query\Parameter;

use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

use App\Repository\TopicRepository;
use App\Repository\PostRepository;

use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Attribute\Model;
use Nelmio\ApiDocBundle\Attribute\Security;
use OpenApi\Attributes as OA;

// #[OA\SecurityScheme(
//     type: 'apiKey',
//     in: 'cookie',
//     name: 'BEARER',
//     securityScheme: 'jwt'
// )]
// class OpenApiSpec
// {}

final class PostController extends AbstractController
{
    #[OA\Response(
        response: Response::HTTP_OK,
        description: "Returns list of posts in given topic",
        content: new OA\JsonContent(
            type: 'object',
            example: 
                [
                    "desc" => "Response",
                    "status" => 200,
                    "value" => [
                        "tid" => 7,
                        "count" => 1,
                        "posts" =>
                        [
                            [
                                "pid" => 8,
                                "uid" => 12,
                                "postCreationTimestamp" => "2025-05-30T17:18:49+00:00",
                                "title" => "Inspiring title",
                                "content" => "Inspiring content",
                                "isArchived" => false,
                                "isClosed" => false
                            ]
                        ]
                    ]
                ]
        )
    )]
    #[OA\Response(
        response: Response::HTTP_BAD_REQUEST,
        description: <<<DESC
            Offset and/or limit and/or tid were ommited in request body, 
            Could not find topic corresponding to tid
        DESC,
        content: new OA\JsonContent(
            type: 'object',
            example:
                [
                    "desc" => "Missing limit key",
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
                    'tid',
                    'offset',
                    'limit'
                ],
                properties: [
                    new OA\Property(
                        property: 'tid',
                        type: 'integer',
                        description: "Topic ID"
                    ),
                    new OA\Property(
                        property: 'offset',
                        type: 'integer',
                        description: "Index from which controller starts listing posts"
                    ),
                    new OA\Property(
                        property: 'limit',
                        type: 'integer',
                        description: "Max count of posts per request. No greater than 128"
                    )
                ]
            ),
            example: [
                "tid" => 19,
                "offset" => 0,
                "limit" => 10
            ]
        )]
    )]
    #[OA\Tag(name: 'Content')]
    #[Route('api/v1/post/get', name: 'api_get_post', methods: ['POST'])]
    public function getPosts(Request $req,
                              EntityManagerInterface $em,
                              TopicRepository $repo): JsonResponse
    {
        $payload = $req->toArray();

        $missing_key = ValidJSONStructure::checkKeys($payload, 'tid', 'offset', 'limit');

        if ($missing_key !== NULL)
        {
            return $this->json(UniformResponse::createInvalid("Missing $missing_key key"),
                               Response::HTTP_BAD_REQUEST);
        }

        $offset = intval($payload["offset"]);
        $limit = intval($payload["limit"]);

        $limit = $limit > 128 ? 128 : $limit;

        $topic = $repo->find($payload['tid']);

        if (!$topic) 
        {
            return $this->json(UniformResponse::createInvalid('Topic not found', Response::HTTP_BAD_REQUEST),
                               Response::HTTP_BAD_REQUEST);
        }

        $result_query_posts = PostDatabaseQueries::getPosts($em, $topic->getTid(), $offset, $limit)
            ->getResult();

        // $result_query_users = array_map(function (array $x) {
        //     if (!$x['display_email'])
        //     {
        //         unset($x['email']);
        //     }
        //     return $x;
        // }, $result_query_users);

        $data = [
            'tid' => $topic->getTid(),
            'count' => count($result_query_posts),
            'posts' => $result_query_posts,
            // 'users' => $result_query_users,
        ];

        return $this->json(UniformResponse::createValid('Response', $data));
    }

    #[OA\Post(
        summary: "Requires JWT from user or higher",
        security: [
            [
                'jwt' => []
            ]
        ]
    )]
    #[OA\RequestBody(
        required: true,
        content: [new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                required: [
                    'tid',
                    'title',
                    'content'
                ],
                properties: [
                    new OA\Property(
                        property: 'tid',
                        type: 'integer',
                        description: "Topic ID"
                    ),
                    new OA\Property(
                        property: 'title',
                        type: 'string',
                        description: "Title for created post"
                    ),
                    new OA\Property(
                        property: 'content',
                        type: 'string',
                        description: "Specify further topic of discussion"
                    )
                ]
            ),
            example: [
                "tid" => 19,
                "title" => 'Inspiring title',
                "content" => 'Inspiring content'
            ]
        )]
    )]
    #[OA\Response(
        response: Response::HTTP_CREATED,
        description: "User successfully created",
        content: new OA\JsonContent(
            type: 'object',
            example: <<<EXAMPLE
                {
                    "desc": "Created new post: Inspiring title",
                    "code": 201
                }
            EXAMPLE
        )
    )]
    #[OA\Response(
        response: Response::HTTP_BAD_REQUEST,
        description: <<<DESC
            tid and/or title and/or content were ommited in request body,
            topic not found
        DESC,
        content: new OA\JsonContent(
            type: 'object',
            example:
                [
                    "desc" => "Missing title key",
                    "status" => 400,
                ]
        )
    )]
    #[OA\Response(
        response: Response::HTTP_UNAUTHORIZED,
        description: "Trying to access endpoint without credentials",
        content: new OA\JsonContent(
            type: 'object',
            example:
                [
                    "desc" => "Unauthorized",
                    "status" => 401,
                ]
        )
    )]
    #[OA\Response(
        response: Response::HTTP_UNPROCESSABLE_ENTITY,
        description: "Constraints not met",
        content: new OA\JsonContent(
            type: 'object',
            example: <<<EXAMPLE
                {
                    "desc": "title: This value is too long. It should have 512 characters or less.",
                    "code": 422
                }
            EXAMPLE
        )
    )]
    #[OA\Tag(name: 'User')]
    #[Route('api/v1/post/add', name: 'api_add_post', methods: ['POST'])]
    public function addPost(Request $req,
                            TokenInterface $sec,
                            ValidatorInterface $validator,
                            TopicRepository $repo,
                            EntityManagerInterface $em): JsonResponse
    {
        $user = $sec->getUser();
        if (!$user) 
        {
            return $this->json(UniformResponse::createInvalid('Unauthorized', Response::HTTP_UNAUTHORIZED), 
                               Response::HTTP_UNAUTHORIZED);
        }

        $payload = $req->toArray();

        $missing_key = ValidJSONStructure::checkKeys($payload, 'tid', 'title', 'content');

        if ($missing_key !== NULL)
        {
            return $this->json(UniformResponse::createInvalid("Missing $missing_key key"),
                               Response::HTTP_BAD_REQUEST);
        }

        $topic = $repo->find($payload['tid']);

        if (!$topic) 
        {
            return $this->json(UniformResponse::createInvalid('Topic not found', Response::HTTP_BAD_REQUEST),
                               Response::HTTP_BAD_REQUEST);
        }

        $post = (new Post())
            ->setTid($topic->getTid())
            ->setUid($user->getUid())
            ->setTitle($payload['title'])
            ->setContent($payload['content'])
            ->setIsArchived(false)
            ->setIsClosed(false)
            ->setUser($user)
            ->setTopic($topic);

        $errors = $validator->validate($post);
        if (count($errors) > 0) {
            return $this->json(UniformResponse::createInvalid(
                                   "{$errors->get(0)->getPropertyPath()}: {$errors->get(0)->getMessage()}", 
                                   Response::HTTP_UNPROCESSABLE_ENTITY),
                               Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $em->persist($post);
        $em->flush();

        return $this->json(UniformResponse::createValid("Created new post: {$post->getTitle()}", NULL, Response::HTTP_CREATED),
                           Response::HTTP_CREATED);
    }


    #[OA\Patch(
        summary: "Requires JWT from user or higher",
        security: [
            [
                'jwt' => []
            ]
        ]
    )]
    #[OA\RequestBody(
        required: true,
        content: [new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                required: [
                    'pid',
                    'content'
                ],
                properties: [
                    new OA\Property(
                        property: 'pid',
                        type: 'integer',
                        description: "Edited post's ID"
                    ),
                    new OA\Property(
                        property: 'content',
                        type: 'string',
                        description: "Additional content"
                    )
                ]
            ),
            example: [
                "pid" => 23,
                "content" => 'Additional inspiring content'
            ]
        )]
    )]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: "Successfully modified post in question",
        content: new OA\JsonContent(
            type: 'object',
            example: <<<EXAMPLE
                {
                    "desc": "Updated",
                    "code": 200
                }
            EXAMPLE
        )
    )]
    #[OA\Response(
        response: Response::HTTP_BAD_REQUEST,
        description: <<<DESC
            pid and/or content were ommited in request body,
            post not found,
            tried to edit post that does not belong to requesting user,
            post is archived or belongs to archived topic
        DESC,
        content: new OA\JsonContent(
            type: 'object',
            example:
                [
                    "desc" => "Missing title key",
                    "status" => 400,
                ]
        )
    )]
    #[OA\Response(
        response: Response::HTTP_UNAUTHORIZED,
        description: "Trying to access endpoint without credentials",
        content: new OA\JsonContent(
            type: 'object',
            example:
                [
                    "desc" => "Unauthorized",
                    "status" => 401,
                ]
        )
    )]
    #[OA\Response(
        response: Response::HTTP_UNPROCESSABLE_ENTITY,
        description: "Constraints not met",
        content: new OA\JsonContent(
            type: 'object',
            example: <<<EXAMPLE
                {
                    "desc": "content: This value is too long. It should have 8192 characters or less.",
                    "code": 422
                }
            EXAMPLE
        )
    )]
    #[OA\Tag(name: 'User')]
    #[Route('api/v1/post/edit', name: 'api_edit_post', methods: ['PATCH'])]
    public function editPost(Request $req,
                             TokenInterface $sec,
                             ValidatorInterface $validator,
                             PostRepository $repo,
                             EntityManagerInterface $em): JsonResponse
    {
        $user = $sec->getUser();
        if (!$user) 
        { 
            return $this->json(UniformResponse::createInvalid('Unauthorized', Response::HTTP_UNAUTHORIZED), 
                               Response::HTTP_UNAUTHORIZED);
        }

        $payload = $req->toArray();

        $missing_key = ValidJSONStructure::checkKeys($payload, 'pid', 'content');

        if ($missing_key !== NULL)
        {
            return $this->json(UniformResponse::createInvalid("Missing $missing_key key"),
                               Response::HTTP_BAD_REQUEST);
        }

        $post = $repo->find($payload['pid']);

        if (!$post) 
        {
            return $this->json(UniformResponse::createInvalid('Post not found'),
                               Response::HTTP_BAD_REQUEST);
        }

        if ($user->getUid() !== $post->getUid())
        {
            return $this->json(UniformResponse::createInvalid('Cannot edit other users\' posts'),
                               Response::HTTP_BAD_REQUEST);
        }

        if ($post->getIsArchived())
        {
            return $this->json(UniformResponse::createInvalid('Post is archived (read only)'),
                               Response::HTTP_BAD_REQUEST);
        }

        if ($post->getTopic()->getIsArchived())
        {
            return $this->json(UniformResponse::createInvalid('Post belongs to archived topic (read only)'),
                               Response::HTTP_BAD_REQUEST);
        }

        $post->setContent("{$post->getContent()}\n[EDIT (" . date("Y-m-d H:i:s") . ")]:\n{$payload['content']}");

        $errors = $validator->validate($post);
        if (count($errors) > 0) {
            return $this->json(UniformResponse::createInvalid(
                                   "{$errors->get(0)->getPropertyPath()}: {$errors->get(0)->getMessage()}", 
                                   Response::HTTP_UNPROCESSABLE_ENTITY),
                               Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $em->persist($post);
        $em->flush();

        return $this->json(UniformResponse::createValid('Updated'),
                           Response::HTTP_OK);
    }
}
